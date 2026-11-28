<?php

namespace app\modules\timetable;

use Yii;
use app\modules\academic\models\SubjectAssign;
use app\modules\course\models\Batch;
use app\modules\course\models\Course;
use app\modules\course\models\Section;
use app\modules\course\models\Subject;
use app\modules\employee\models\Employee;
use app\modules\employee\models\EmployeeInfo;
use yii\db\Query;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\timetable\controllers';
    public $defaultRoute = 'timetable/index';

    /** @var int Rows per page in tabular timetable list */
    public $pageSize = 25;

    /**
     * Optional dropdown data or callables returning [id => label].
     * Supported keys: academic_session, room, course, batch, section, subject, employee.
     * If academic_session or room tables exist in DB, they are detected automatically;
     * otherwise fields fall back cleanly to numeric inputs.
     *
     * @var array<string, array|callable>
     */
    public $lists = [];

    /**
     * Resolves lookup lists for forms, filters, and display views.
     *
     * @return array<string, array<int|string, string>>
     */
    public function resolveLists(): array
    {
        return [
            'academic_session' => $this->resolveCustomOrTableList(
                'academic_session',
                ['academic_sessions', 'academic_session', 'coaching_academic_sessions', 'sessions'],
                ['id', 'academic_session_id', 'session_id'],
                ['name', 'session_name', 'title', 'academic_year', 'year', 'code']
            ),
            'room' => $this->resolveCustomOrTableList(
                'room',
                ['rooms', 'room', 'coaching_rooms', 'classrooms'],
                ['id', 'room_id', 'classroom_id'],
                ['name', 'room_name', 'room_no', 'room_number', 'code', 'title']
            ),
            'course' => $this->resolveConfiguredList('course') ?? $this->defaultCourseOptions(),
            'batch' => $this->resolveConfiguredList('batch') ?? $this->defaultBatchOptions(),
            'section' => $this->resolveConfiguredList('section') ?? $this->defaultSectionOptions(),
            'subject' => $this->resolveConfiguredList('subject') ?? $this->defaultSubjectOptions(),
            'employee' => $this->resolveConfiguredList('employee') ?? $this->defaultEmployeeOptions(),
        ];
    }

    /**
     * Returns relational metadata maps used for client-side dependent dropdown filtering
     * and automatic teacher suggestion in timetable forms.
     */
    public function resolvePlacementMeta(): array
    {
        $batchByCourse = [];
        $sectionByCourseAndBatch = [];
        $subjectByCourse = [];
        $assignedTeachers = [];

        try {
            if (class_exists(Batch::class)) {
                foreach (Batch::find()->select(['id', 'course_id'])->asArray()->all() as $row) {
                    $batchByCourse[(int) $row['id']] = (int) $row['course_id'];
                }
            }
            if (class_exists(Section::class)) {
                foreach (Section::find()->select(['id', 'course_id', 'batch_id'])->asArray()->all() as $row) {
                    $sectionByCourseAndBatch[(int) $row['id']] = [
                        (int) $row['course_id'],
                        (int) $row['batch_id'],
                    ];
                }
            }
            $subjectIdByCodeAndCourse = [];
            if (class_exists(Subject::class)) {
                foreach (Subject::find()->select(['id', 'code', 'course_id'])->asArray()->all() as $row) {
                    $subjectId = (int) $row['id'];
                    $courseId = (int) $row['course_id'];
                    $subjectByCourse[$subjectId] = $courseId;
                    $codeKey = ($row['code'] !== null && $row['code'] !== '') ? (string) $row['code'] : (string) $subjectId;
                    $subjectIdByCodeAndCourse[$courseId . ':' . $codeKey] = $subjectId;
                    $subjectIdByCodeAndCourse[$courseId . ':' . $subjectId] = $subjectId;
                }
            }
            if (
                class_exists(SubjectAssign::class)
                && Yii::$app->db->schema->getTableSchema(SubjectAssign::tableName(), true) !== null
            ) {
                foreach (SubjectAssign::find()->asArray()->all() as $assign) {
                    $cId = (int) $assign['course_id'];
                    $bId = (int) $assign['Batch_id'];
                    $secId = (int) $assign['section_id'];
                    $empId = (int) $assign['employ_master_id'];
                    $sCode = (string) $assign['subject_code'];
                    $resolvedSubjectId = $subjectIdByCodeAndCourse[$cId . ':' . $sCode] ?? (ctype_digit($sCode) ? (int) $sCode : null);
                    if ($resolvedSubjectId) {
                        $assignedTeachers["{$cId}:{$bId}:{$secId}:{$resolvedSubjectId}"] = $empId;
                        if (!isset($assignedTeachers["{$cId}:{$bId}:0:{$resolvedSubjectId}"])) {
                            $assignedTeachers["{$cId}:{$bId}:0:{$resolvedSubjectId}"] = $empId;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Yii::warning('Failed resolving timetable placement metadata: ' . $e->getMessage(), __METHOD__);
        }

        return [
            'batchByCourse' => $batchByCourse,
            'sectionByCourseAndBatch' => $sectionByCourseAndBatch,
            'subjectByCourse' => $subjectByCourse,
            'assignedTeachers' => $assignedTeachers,
        ];
    }

    private function resolveConfiguredList(string $key): ?array
    {
        if (!array_key_exists($key, $this->lists)) {
            return null;
        }
        $entry = $this->lists[$key];
        $data = is_callable($entry) ? call_user_func($entry) : $entry;
        return is_array($data) ? $data : [];
    }

    private function resolveCustomOrTableList(
        string $key,
        array $candidateTables,
        array $pkCandidates,
        array $labelCandidates
    ): array {
        $configured = $this->resolveConfiguredList($key);
        if ($configured !== null) {
            return $configured;
        }

        try {
            $schema = Yii::$app->db->schema;
            foreach ($candidateTables as $tableName) {
                $tableSchema = $schema->getTableSchema($tableName);
                if ($tableSchema === null) {
                    continue;
                }

                $pkCol = $tableSchema->primaryKey[0] ?? null;
                if ($pkCol === null) {
                    foreach ($pkCandidates as $candidate) {
                        if ($tableSchema->getColumn($candidate) !== null) {
                            $pkCol = $candidate;
                            break;
                        }
                    }
                }
                if ($pkCol === null) {
                    continue;
                }

                $labelCol = null;
                foreach ($labelCandidates as $candidate) {
                    if ($tableSchema->getColumn($candidate) !== null) {
                        $labelCol = $candidate;
                        break;
                    }
                }

                $selectCols = $labelCol ? [$pkCol, $labelCol] : [$pkCol];
                $rows = (new Query())
                    ->select($selectCols)
                    ->from($tableName)
                    ->orderBy([$labelCol ?: $pkCol => SORT_ASC])
                    ->all(Yii::$app->db);

                $options = [];
                foreach ($rows as $row) {
                    $id = $row[$pkCol];
                    $options[$id] = $labelCol && isset($row[$labelCol]) && $row[$labelCol] !== ''
                        ? (string) $row[$labelCol]
                        : ucfirst(str_replace('_', ' ', $key)) . ' #' . $id;
                }
                return $options;
            }
        } catch (\Throwable $e) {
            // Gracefully fall back to numeric input if lookup table does not exist
        }

        return [];
    }

    private function defaultCourseOptions(): array
    {
        if (!class_exists(Course::class)) {
            return [];
        }
        try {
            $options = [];
            foreach (Course::find()->orderBy(['name' => SORT_ASC])->all() as $course) {
                $label = $course->name ?: ('Course #' . $course->id);
                if (!empty($course->code)) {
                    $label .= ' (' . $course->code . ')';
                }
                $options[(int) $course->id] = $label;
            }
            return $options;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function defaultBatchOptions(): array
    {
        if (!class_exists(Batch::class)) {
            return [];
        }
        try {
            $options = [];
            foreach (Batch::find()->with('course')->orderBy(['name' => SORT_ASC])->all() as $batch) {
                $label = $batch->name ?: ('Batch #' . $batch->id);
                if ($batch->course && $batch->course->name) {
                    $label .= ' (' . $batch->course->name . ')';
                }
                $options[(int) $batch->id] = $label;
            }
            return $options;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function defaultSectionOptions(): array
    {
        if (!class_exists(Section::class)) {
            return [];
        }
        try {
            $options = [];
            foreach (Section::find()->with(['course', 'batch'])->orderBy(['name' => SORT_ASC])->all() as $section) {
                $parts = array_filter([
                    $section->course ? $section->course->name : null,
                    $section->batch ? $section->batch->name : null,
                    $section->name ?: ('Section #' . $section->id),
                ]);
                $options[(int) $section->id] = implode(' / ', $parts);
            }
            return $options;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function defaultSubjectOptions(): array
    {
        if (!class_exists(Subject::class)) {
            return [];
        }
        try {
            $options = [];
            foreach (Subject::find()->with('course')->orderBy(['name' => SORT_ASC])->all() as $subject) {
                $label = $subject->name ?: ('Subject #' . $subject->id);
                if (!empty($subject->code)) {
                    $label .= ' (' . $subject->code . ')';
                }
                $options[(int) $subject->id] = $label;
            }
            return $options;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function defaultEmployeeOptions(): array
    {
        if (!class_exists(Employee::class)) {
            return [];
        }
        try {
            $employees = Employee::find()
                ->joinWith('info')
                ->where([
                    'emp_master.is_status' => Employee::STATUS_ACTIVE,
                    'emp_info.employee_type' => EmployeeInfo::TYPE_TEACHING,
                ])
                ->orderBy(['emp_info.emp_first_name' => SORT_ASC, 'emp_master.emp_master_id' => SORT_ASC])
                ->all();

            // Fallback to all active employees if no teaching-typed employees exist yet
            if (empty($employees)) {
                $employees = Employee::find()
                    ->joinWith('info')
                    ->where(['emp_master.is_status' => Employee::STATUS_ACTIVE])
                    ->orderBy(['emp_info.emp_first_name' => SORT_ASC, 'emp_master.emp_master_id' => SORT_ASC])
                    ->all();
            }

            $options = [];
            foreach ($employees as $employee) {
                $label = $employee->info ? $employee->info->fullName : ('Employee #' . $employee->emp_master_id);
                if ($employee->info && !empty($employee->info->employee_code)) {
                    $label .= ' (' . $employee->info->employee_code . ')';
                }
                $options[(int) $employee->emp_master_id] = $label;
            }
            return $options;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
