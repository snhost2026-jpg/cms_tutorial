<?php

namespace app\modules\exam\controllers;

use app\modules\exam\models\Exam;
use app\modules\exam\models\ExamAttempt;
use app\modules\exam\models\ExamAttemptAnswer;
use app\modules\exam\models\ExamQuestion;
use app\modules\exam\models\ExamQuestionMap;
use app\modules\student\models\StuMaster;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Student-facing Online Exam Portal ("My Exams").
 */
class TakeController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'start' => ['POST'],
                    'save-answer' => ['POST'],
                    'submit' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists published exams eligible for the logged-in student (or all published exams for admins/teachers).
     */
    public function actionIndex()
    {
        $userId = (int) Yii::$app->user->id;
        $student = $this->findCurrentStudent($userId);
        $isAdmin = $this->isAdminOrStaff();

        $query = Exam::find()
            ->with(['course', 'batch', 'section', 'subject'])
            ->where(['status' => Exam::STATUS_PUBLISHED])
            ->orderBy(['start_time' => SORT_DESC, 'id' => SORT_DESC]);

        if ($student && !$isAdmin) {
            // Auto-match student's Course, Batch, and optional Section (null on exam = open to all)
            if ($student->stu_master_course_id) {
                $query->andWhere(['or', ['course_id' => $student->stu_master_course_id], ['course_id' => null]]);
            } else {
                $query->andWhere(['course_id' => null]);
            }

            if ($student->stu_master_batch_id) {
                $query->andWhere(['or', ['batch_id' => $student->stu_master_batch_id], ['batch_id' => null]]);
            } else {
                $query->andWhere(['batch_id' => null]);
            }

            if ($student->stu_master_section_id) {
                $query->andWhere(['or', ['section_id' => $student->stu_master_section_id], ['section_id' => null]]);
            } else {
                $query->andWhere(['section_id' => null]);
            }
        } elseif (!$student && !$isAdmin) {
            // Non-admin user without a student placement sees only general (unrestricted) exams
            $query->andWhere(['course_id' => null, 'batch_id' => null, 'section_id' => null]);
        }

        $exams = $query->all();

        $attempts = ExamAttempt::find()
            ->where(['user_id' => $userId])
            ->indexBy('exam_id')
            ->all();

        return $this->render('index', [
            'exams' => $exams,
            'attempts' => $attempts,
            'student' => $student,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function actionInstructions($id)
    {
        $exam = $this->findEligibleExam($id);
        $userId = (int) Yii::$app->user->id;
        $attempt = ExamAttempt::findOne(['exam_id' => $exam->id, 'user_id' => $userId]);

        if ($attempt && $attempt->status === ExamAttempt::STATUS_SUBMITTED) {
            return $this->redirect(['result', 'attempt_id' => $attempt->id]);
        }

        return $this->render('instructions', [
            'exam' => $exam,
            'attempt' => $attempt,
            'questionCount' => $exam->getQuestionCount(),
            'totalMarks' => $exam->getTotalMarks(),
        ]);
    }

    public function actionStart($id)
    {
        $exam = $this->findEligibleExam($id);
        $userId = (int) Yii::$app->user->id;

        $attempt = ExamAttempt::findOne(['exam_id' => $exam->id, 'user_id' => $userId]);
        if ($attempt) {
            if ($attempt->status === ExamAttempt::STATUS_SUBMITTED) {
                return $this->redirect(['result', 'attempt_id' => $attempt->id]);
            }
            return $this->redirect(['paper', 'attempt_id' => $attempt->id]);
        }

        if (!$exam->isOpenNow()) {
            Yii::$app->session->setFlash('error', 'This exam is not currently open for starting.');
            return $this->redirect(['instructions', 'id' => $exam->id]);
        }

        $questionIds = array_map('intval', ExamQuestionMap::find()
            ->select('question_id')
            ->where(['exam_id' => $exam->id])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->column());

        if (empty($questionIds)) {
            Yii::$app->session->setFlash('error', 'This exam has no questions configured yet.');
            return $this->redirect(['index']);
        }

        if ($exam->shuffle_questions) {
            shuffle($questionIds);
        }

        $student = $this->findCurrentStudent($userId);

        $attempt = new ExamAttempt([
            'exam_id' => $exam->id,
            'user_id' => $userId,
            'stu_master_id' => $student ? $student->stu_master_id : null,
            'question_order' => json_encode($questionIds),
            'started_at' => date('Y-m-d H:i:s'),
            'total_questions' => count($questionIds),
            'total_marks' => $exam->getTotalMarks(),
            'status' => ExamAttempt::STATUS_IN_PROGRESS,
        ]);
        $attempt->save(false);

        return $this->redirect(['paper', 'attempt_id' => $attempt->id]);
    }

    public function actionPaper($attempt_id)
    {
        $attempt = $this->findOwnedAttempt($attempt_id);

        if ($attempt->status === ExamAttempt::STATUS_SUBMITTED) {
            return $this->redirect(['result', 'attempt_id' => $attempt->id]);
        }

        // Server-side time enforcement: auto-submit if time has expired
        if ($attempt->isTimeExpired()) {
            $attempt->gradeAndSubmit();
            Yii::$app->session->setFlash('info', 'Time expired. Your exam was automatically submitted.');
            return $this->redirect(['result', 'attempt_id' => $attempt->id]);
        }

        $questionIds = $attempt->getOrderedQuestionIds();
        $questions = ExamQuestion::find()->where(['id' => $questionIds])->indexBy('id')->all();
        $orderedQuestions = [];
        foreach ($questionIds as $qid) {
            if (isset($questions[$qid])) {
                $orderedQuestions[] = $questions[$qid];
            }
        }

        $savedAnswers = ExamAttemptAnswer::find()
            ->where(['attempt_id' => $attempt->id])
            ->indexBy('question_id')
            ->all();

        return $this->render('paper', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'questions' => $orderedQuestions,
            'savedAnswers' => $savedAnswers,
            'remainingSeconds' => $attempt->getRemainingSeconds(),
        ]);
    }

    /**
     * AJAX endpoint to autosave a student's selected option or mark-for-review flag.
     */
    public function actionSaveAnswer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $attemptId = (int) Yii::$app->request->post('attempt_id');
        $questionId = (int) Yii::$app->request->post('question_id');
        $selectedOption = Yii::$app->request->post('selected_option');
        $isMarkedReview = Yii::$app->request->post('is_marked_review');

        $attempt = ExamAttempt::findOne(['id' => $attemptId, 'user_id' => (int) Yii::$app->user->id]);
        if (!$attempt) {
            return ['ok' => false, 'message' => 'Attempt not found.'];
        }
        if ($attempt->status === ExamAttempt::STATUS_SUBMITTED) {
            return ['ok' => false, 'expired' => true, 'message' => 'Exam already submitted.'];
        }
        if ($attempt->isTimeExpired()) {
            $attempt->gradeAndSubmit();
            return ['ok' => false, 'expired' => true, 'message' => 'Time expired. Exam auto-submitted.'];
        }

        $allowedIds = $attempt->getOrderedQuestionIds();
        if (!in_array($questionId, $allowedIds, true)) {
            return ['ok' => false, 'message' => 'Invalid question.'];
        }

        $answer = ExamAttemptAnswer::findOne(['attempt_id' => $attempt->id, 'question_id' => $questionId]);
        if (!$answer) {
            $answer = new ExamAttemptAnswer([
                'attempt_id' => $attempt->id,
                'question_id' => $questionId,
            ]);
        }

        if ($selectedOption !== null) {
            $opt = strtoupper(trim((string) $selectedOption));
            $answer->selected_option = in_array($opt, ['A', 'B', 'C', 'D'], true) ? $opt : null;
        }
        if ($isMarkedReview !== null) {
            $answer->is_marked_review = (int) ((bool) $isMarkedReview);
        }
        $answer->updated_at = date('Y-m-d H:i:s');

        if ($answer->save()) {
            return [
                'ok' => true,
                'selected_option' => $answer->selected_option,
                'is_marked_review' => (bool) $answer->is_marked_review,
                'remaining_seconds' => $attempt->getRemainingSeconds(),
            ];
        }

        return ['ok' => false, 'message' => 'Could not save answer.'];
    }

    public function actionSubmit($attempt_id)
    {
        $attempt = $this->findOwnedAttempt($attempt_id);
        $attempt->gradeAndSubmit();
        Yii::$app->session->setFlash('success', 'Your exam has been submitted successfully.');
        return $this->redirect(['result', 'attempt_id' => $attempt->id]);
    }

    public function actionResult($attempt_id)
    {
        $attempt = $this->findOwnedAttempt($attempt_id);
        if ($attempt->status !== ExamAttempt::STATUS_SUBMITTED) {
            return $this->redirect(['paper', 'attempt_id' => $attempt->id]);
        }

        $exam = $attempt->exam;
        $showResult = $exam && ((bool) $exam->show_result || $this->isAdminOrStaff());

        $questionIds = $attempt->getOrderedQuestionIds();
        $questions = $showResult
            ? ExamQuestion::find()->where(['id' => $questionIds])->indexBy('id')->all()
            : [];
        $answers = $showResult
            ? ExamAttemptAnswer::find()->where(['attempt_id' => $attempt->id])->indexBy('question_id')->all()
            : [];

        return $this->render('result', [
            'attempt' => $attempt,
            'exam' => $exam,
            'showResult' => $showResult,
            'questionIds' => $questionIds,
            'questions' => $questions,
            'answers' => $answers,
        ]);
    }

    protected function findCurrentStudent(int $userId): ?StuMaster
    {
        try {
            return StuMaster::find()->where(['stu_master_user_id' => $userId])->one();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function isAdminOrStaff(): bool
    {
        return Yii::$app->user->can('SuperAdmin')
            || Yii::$app->user->can('admin')
            || Yii::$app->user->can('manager')
            || Yii::$app->user->can('manageExam');
    }

    protected function findEligibleExam($id): Exam
    {
        $exam = Exam::find()
            ->with(['course', 'batch', 'section', 'subject'])
            ->where(['id' => (int) $id, 'status' => Exam::STATUS_PUBLISHED])
            ->one();

        if (!$exam) {
            throw new NotFoundHttpException('Exam not found or not published.');
        }

        if ($this->isAdminOrStaff()) {
            return $exam;
        }

        $student = $this->findCurrentStudent((int) Yii::$app->user->id);
        if ($exam->course_id && (!$student || (int) $student->stu_master_course_id !== (int) $exam->course_id)) {
            throw new ForbiddenHttpException('You are not eligible for this exam.');
        }
        if ($exam->batch_id && (!$student || (int) $student->stu_master_batch_id !== (int) $exam->batch_id)) {
            throw new ForbiddenHttpException('You are not eligible for this exam.');
        }
        if ($exam->section_id && (!$student || (int) $student->stu_master_section_id !== (int) $exam->section_id)) {
            throw new ForbiddenHttpException('You are not eligible for this exam.');
        }

        return $exam;
    }

    protected function findOwnedAttempt($attemptId): ExamAttempt
    {
        $attempt = ExamAttempt::find()
            ->with(['exam'])
            ->where(['id' => (int) $attemptId, 'user_id' => (int) Yii::$app->user->id])
            ->one();

        if (!$attempt) {
            throw new NotFoundHttpException('Exam attempt not found.');
        }

        return $attempt;
    }
}
