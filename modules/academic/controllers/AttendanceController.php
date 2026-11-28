<?php

namespace app\modules\academic\controllers;

use Yii;
use app\components\AccessHelper;
use app\modules\course\models\Section;
use app\modules\academic\models\StudentAttendance;
use app\modules\course\models\Subject;
use app\modules\student\models\StuMaster;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class AttendanceController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'history', 'view'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (
            !Yii::$app->user->isGuest
            && !Yii::$app->user->can('SuperAdmin')
            && !Yii::$app->user->can('admin')
            && !Yii::$app->user->can('manageAcademic')
            && !Yii::$app->user->can('manageStudent')
            && !Yii::$app->user->can('manageRbac')
            && !AccessHelper::checkRoute('/' . $action->getUniqueId())
            && !AccessHelper::checkRoute('/academic/*')
        ) {
            throw new ForbiddenHttpException('You are not allowed to manage attendance.');
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $isPost = $request->isPost;
        $sectionId = $this->parseId($isPost ? $request->post('section_id') : $request->get('section_id'), 'section');
        $subjectId = $this->parseId($isPost ? $request->post('subject_id') : $request->get('subject_id'), 'subject');
        $date = $isPost ? $request->post('attendance_date') : $request->get('date', date('Y-m-d'));
        if (!is_string($date) || !$this->isValidDate($date)) {
            throw new BadRequestHttpException('Choose a valid attendance date.');
        }

        $sections = $this->sectionOptions();
        $section = null;
        $subject = null;
        $subjects = [];
        $students = [];
        $attendanceByStudent = [];

        if ($sectionId !== null) {
            $section = Section::findOne($sectionId);
            if ($section === null) {
                throw new NotFoundHttpException('The selected section does not exist.');
            }

            $subjects = $this->subjectOptions($section);

            if ($subjectId !== null) {
                $subject = Subject::find()
                    ->where(['id' => $subjectId, 'course_id' => $section->course_id])
                    ->one();
                if ($subject === null) {
                    throw new NotFoundHttpException('The selected subject is not offered for this section.');
                }

                $students = StuMaster::find()
                    ->joinWith('info')
                    ->andWhere([
                        'stu_master.stu_master_section_id' => $section->id,
                        'stu_master.is_status' => StuMaster::STATUS_ACTIVE,
                    ])
                    ->orderBy([
                        'stu_info.stu_unique_id' => SORT_ASC,
                        'stu_info.stu_first_name' => SORT_ASC,
                    ])
                    ->all();

                $attendanceRecords = StudentAttendance::find()
                    ->select(['student_id', 'status'])
                    ->where([
                        'section_id' => $section->id,
                        'subject_id' => $subject->id,
                        'attendance_date' => $date,
                    ])
                    ->asArray()
                    ->all();
                foreach ($attendanceRecords as $record) {
                    $attendanceByStudent[(int) $record['student_id']] = $record['status'];
                }
            } elseif ($isPost) {
                throw new BadRequestHttpException('Select a subject before submitting attendance.');
            }
        } elseif ($isPost) {
            throw new BadRequestHttpException('Select a section before submitting attendance.');
        }

        if ($isPost) {
            $submitted = $request->post('attendance');
            $errors = $this->validateSubmittedAttendance($students, $submitted);

            if (!$errors) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    foreach ($students as $student) {
                        $studentId = (int) $student->stu_master_id;
                        $record = StudentAttendance::findOne([
                            'student_id' => $studentId,
                            'section_id' => $section->id,
                            'subject_id' => $subject->id,
                            'attendance_date' => $date,
                        ]);
                        if ($record === null) {
                            $record = new StudentAttendance();
                            $record->created_at = time();
                        }

                        $record->student_id = $studentId;
                        $record->section_id = $section->id;
                        $record->subject_id = $subject->id;
                        $record->attendance_date = $date;
                        $record->status = $submitted[$studentId];
                        $record->marked_by = Yii::$app->user->id;
                        $record->updated_at = time();

                        if (!$record->save()) {
                            throw new \RuntimeException(
                                'Could not save attendance for student ' . $studentId . ': '
                                . json_encode($record->getErrors())
                            );
                        }
                    }

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Attendance saved for ' . count($students) . ' students.');
                    return $this->redirect([
                        'index',
                        'section_id' => $section->id,
                        'subject_id' => $subject->id,
                        'date' => $date,
                    ]);
                } catch (\Throwable $e) {
                    if ($transaction->isActive) {
                        $transaction->rollBack();
                    }
                    Yii::error($e->getMessage(), __METHOD__);
                    $errors[] = 'Attendance could not be saved: ' . $e->getMessage();
                }
            }

            Yii::$app->session->setFlash('error', implode(' ', $errors));
            $attendanceByStudent = [];
            if (is_array($submitted)) {
                foreach ($students as $student) {
                    $studentId = (int) $student->stu_master_id;
                    if (isset($submitted[$studentId]) && is_string($submitted[$studentId])) {
                        $attendanceByStudent[$studentId] = $submitted[$studentId];
                    }
                }
            }
        }

        return $this->render('index', [
            'sections' => $sections,
            'section' => $section,
            'subjects' => $subjects,
            'subject' => $subject,
            'students' => $students,
            'attendanceByStudent' => $attendanceByStudent,
            'date' => $date,
        ]);
    }

    public function actionHistory()
    {
        $request = Yii::$app->request;
        $sectionId = $this->parseId($request->get('section_id'), 'section');
        $subjectId = $this->parseId($request->get('subject_id'), 'subject');
        $dateFrom = $this->parseOptionalDate($request->get('date_from'), 'start');
        $dateTo = $this->parseOptionalDate($request->get('date_to'), 'end');

        if ($dateFrom !== null && $dateTo !== null && $dateFrom > $dateTo) {
            throw new BadRequestHttpException('The start date must not be later than the end date.');
        }

        $section = null;
        if ($sectionId !== null) {
            $section = Section::findOne($sectionId);
            if ($section === null) {
                throw new NotFoundHttpException('The selected section does not exist.');
            }
        }

        if ($subjectId !== null && Subject::findOne($subjectId) === null) {
            throw new NotFoundHttpException('The selected subject does not exist.');
        }

        $sessionsQuery = (new Query())
            ->select([
                'section_id' => 'attendance.section_id',
                'section_name' => 'section.name',
                'subject_id' => 'attendance.subject_id',
                'subject_name' => 'subject.name',
                'attendance_date' => 'attendance.attendance_date',
                'total_count' => new Expression('COUNT(*)'),
                'present_count' => new Expression(
                    'SUM(CASE WHEN attendance.status = :present THEN 1 ELSE 0 END)',
                    [':present' => StudentAttendance::STATUS_PRESENT]
                ),
                'absent_count' => new Expression(
                    'SUM(CASE WHEN attendance.status = :absent THEN 1 ELSE 0 END)',
                    [':absent' => StudentAttendance::STATUS_ABSENT]
                ),
                'late_count' => new Expression(
                    'SUM(CASE WHEN attendance.status = :late THEN 1 ELSE 0 END)',
                    [':late' => StudentAttendance::STATUS_LATE]
                ),
                'excused_count' => new Expression(
                    'SUM(CASE WHEN attendance.status = :excused THEN 1 ELSE 0 END)',
                    [':excused' => StudentAttendance::STATUS_EXCUSED]
                ),
            ])
            ->from(['attendance' => StudentAttendance::tableName()])
            ->innerJoin(['section' => Section::tableName()], 'section.id = attendance.section_id')
            ->innerJoin(['subject' => Subject::tableName()], 'subject.id = attendance.subject_id')
            ->groupBy([
                'attendance.section_id',
                'section.name',
                'attendance.subject_id',
                'subject.name',
                'attendance.attendance_date',
            ]);

        if ($sectionId !== null) {
            $sessionsQuery->andWhere(['attendance.section_id' => $sectionId]);
        }
        if ($subjectId !== null) {
            $sessionsQuery->andWhere(['attendance.subject_id' => $subjectId]);
        }
        if ($dateFrom !== null) {
            $sessionsQuery->andWhere(['>=', 'attendance.attendance_date', $dateFrom]);
        }
        if ($dateTo !== null) {
            $sessionsQuery->andWhere(['<=', 'attendance.attendance_date', $dateTo]);
        }

        $query = (new Query())
            ->select('*')
            ->from(['session_summary' => $sessionsQuery])
            ->orderBy([
                'attendance_date' => SORT_DESC,
                'section_name' => SORT_ASC,
                'subject_name' => SORT_ASC,
            ]);

        return $this->render('history', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 25],
                'sort' => false,
            ]),
            'sections' => $this->sectionOptions(),
            'subjects' => $this->subjectOptions(),
            'sectionId' => $sectionId,
            'subjectId' => $subjectId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function actionView($section_id, $subject_id, $date)
    {
        $sectionId = $this->parseId($section_id, 'section');
        $subjectId = $this->parseId($subject_id, 'subject');
        if (!is_string($date) || !$this->isValidDate($date)) {
            throw new BadRequestHttpException('Choose a valid attendance date.');
        }

        $section = Section::findOne($sectionId);
        if ($section === null) {
            throw new NotFoundHttpException('The selected section does not exist.');
        }

        $subject = Subject::find()
            ->where(['id' => $subjectId, 'course_id' => $section->course_id])
            ->one();
        if ($subject === null) {
            throw new NotFoundHttpException('The selected subject is not offered for this section.');
        }

        $records = (new Query())
            ->select([
                'registration_no' => 'info.stu_unique_id',
                'first_name' => 'info.stu_first_name',
                'middle_name' => 'info.stu_middle_name',
                'last_name' => 'info.stu_last_name',
                'status' => 'attendance.status',
            ])
            ->from(['attendance' => StudentAttendance::tableName()])
            ->leftJoin(['student' => StuMaster::tableName()], 'student.stu_master_id = attendance.student_id')
            ->leftJoin(['info' => \app\modules\student\models\StuInfo::tableName()], 'info.stu_info_id = student.stu_master_stu_info_id')
            ->where([
                'attendance.section_id' => $sectionId,
                'attendance.subject_id' => $subjectId,
                'attendance.attendance_date' => $date,
            ])
            ->orderBy(['info.stu_unique_id' => SORT_ASC, 'info.stu_first_name' => SORT_ASC])
            ->all();

        return $this->render('view', [
            'section' => $section,
            'subject' => $subject,
            'sectionLabel' => $this->sectionOptions()[$section->id] ?? $section->name,
            'date' => $date,
            'records' => $records,
        ]);
    }

    private function sectionOptions()
    {
        $options = [];
        foreach (Section::find()->with(['course', 'batch'])->orderBy(['name' => SORT_ASC])->all() as $section) {
            $parts = [];
            if ($section->course) {
                $parts[] = $section->course->name;
            }
            if ($section->batch) {
                $parts[] = $section->batch->name;
            }
            $parts[] = $section->name;
            $parts = array_filter($parts, function ($part) {
                return $part !== null && $part !== '';
            });
            $options[$section->id] = implode(' / ', $parts);
        }

        return $options;
    }

    private function subjectOptions(?Section $section = null)
    {
        if ($section !== null && empty($section->course_id)) {
            return [];
        }

        $options = [];
        $query = Subject::find();
        if ($section !== null) {
            $query->where(['course_id' => $section->course_id]);
        }
        $records = $query->orderBy(['name' => SORT_ASC])->all();
        foreach ($records as $subject) {
            $label = $subject->name;
            if ($subject->code) {
                $label .= ' (' . $subject->code . ')';
            }
            $options[$subject->id] = $label;
        }

        return $options;
    }

    private function validateSubmittedAttendance(array $students, $submitted)
    {
        if (!is_array($submitted)) {
            return ['Submit a status for every active student in this section.'];
        }
        if (!$students) {
            return ['There are no active students in this section to mark.'];
        }

        $expectedIds = array_map(function ($student) {
            return (string) $student->stu_master_id;
        }, $students);
        $submittedIds = array_map('strval', array_keys($submitted));
        sort($expectedIds, SORT_STRING);
        sort($submittedIds, SORT_STRING);

        $errors = [];
        if ($expectedIds !== $submittedIds) {
            $errors[] = 'The roster changed or the submitted list is incomplete. Reload the page and try again.';
        }

        $validStatuses = array_keys(StudentAttendance::statusOptions());
        foreach ($students as $student) {
            $studentId = (int) $student->stu_master_id;
            if (
                !isset($submitted[$studentId])
                || !is_string($submitted[$studentId])
                || !in_array($submitted[$studentId], $validStatuses, true)
            ) {
                $errors[] = 'Choose a valid attendance status for every student.';
                break;
            }
        }

        return $errors;
    }

    private function parseId($value, $name)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_scalar($value) || !ctype_digit((string) $value) || (int) $value < 1) {
            throw new BadRequestHttpException('The selected ' . $name . ' is invalid.');
        }

        return (int) $value;
    }

    private function isValidDate($date)
    {
        $parsed = \DateTime::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private function parseOptionalDate($value, $name)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value) || !$this->isValidDate($value)) {
            throw new BadRequestHttpException('The ' . $name . ' date is invalid.');
        }

        return $value;
    }
}
