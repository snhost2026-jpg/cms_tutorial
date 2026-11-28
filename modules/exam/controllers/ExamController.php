<?php

namespace app\modules\exam\controllers;

use app\modules\exam\models\Exam;
use app\modules\exam\models\ExamAttempt;
use app\modules\exam\models\ExamAttemptAnswer;
use app\modules\exam\models\ExamQuestion;
use app\modules\exam\models\ExamQuestionMap;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ExamController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'set-status' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $courseId = $request->get('course_id');
        $batchId = $request->get('batch_id');
        $status = $request->get('status');
        $q = trim((string) $request->get('q', ''));

        $query = Exam::find()
            ->with(['course', 'batch', 'section', 'subject'])
            ->orderBy(['id' => SORT_DESC]);

        if ($courseId !== null && $courseId !== '') {
            $query->andWhere(['course_id' => (int) $courseId]);
        }
        if ($batchId !== null && $batchId !== '') {
            $query->andWhere(['batch_id' => (int) $batchId]);
        }
        if ($status !== null && $status !== '') {
            $query->andWhere(['status' => $status]);
        }
        if ($q !== '') {
            $query->andWhere(['like', 'title', $q]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $this->module->pageSize],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'lists' => $this->module->resolveAcademicLists(),
            'filters' => [
                'course_id' => $courseId,
                'batch_id' => $batchId,
                'status' => $status,
                'q' => $q,
            ],
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $assignedQuestions = ExamQuestion::find()
            ->innerJoin('exam_question_map m', 'm.question_id = exam_questions.id')
            ->where(['m.exam_id' => $model->id])
            ->orderBy(['m.sort_order' => SORT_ASC, 'm.id' => SORT_ASC])
            ->all();

        $attemptCount = (int) ExamAttempt::find()->where(['exam_id' => $model->id])->count();
        $submittedCount = (int) ExamAttempt::find()->where(['exam_id' => $model->id, 'status' => ExamAttempt::STATUS_SUBMITTED])->count();

        return $this->render('view', [
            'model' => $model,
            'assignedQuestions' => $assignedQuestions,
            'attemptCount' => $attemptCount,
            'submittedCount' => $submittedCount,
        ]);
    }

    public function actionCreate()
    {
        $model = new Exam();
        $model->status = Exam::STATUS_DRAFT;
        $model->show_result = 1;
        $model->shuffle_questions = 0;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Exam created. Now select questions from the Question Bank.');
            return $this->redirect(['questions', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'lists' => $this->module->resolveAcademicLists(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Exam updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'lists' => $this->module->resolveAcademicLists(),
        ]);
    }

    public function actionSetStatus($id, $status)
    {
        $model = $this->findModel($id);
        if (!array_key_exists($status, Exam::statusOptions())) {
            Yii::$app->session->setFlash('error', 'Invalid status.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        if ($status === Exam::STATUS_PUBLISHED && $model->getQuestionCount() === 0) {
            Yii::$app->session->setFlash('error', 'Cannot publish an exam with zero questions. Please add at least one question first.');
            return $this->redirect(['questions', 'id' => $model->id]);
        }

        $model->status = $status;
        $model->save(false);
        Yii::$app->session->setFlash('success', 'Exam status updated to ' . Exam::statusOptions()[$status] . '.');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Pick questions from the Question Bank for this exam, or quick-add a new question directly.
     */
    public function actionQuestions($id)
    {
        $exam = $this->findModel($id);
        $request = Yii::$app->request;

        // Handle quick-add new question directly into this exam
        $quickQuestion = new ExamQuestion();
        $quickQuestion->course_id = $exam->course_id;
        $quickQuestion->subject_id = $exam->subject_id;
        $quickQuestion->marks = 1;
        $quickQuestion->difficulty = ExamQuestion::DIFFICULTY_MEDIUM;
        $quickQuestion->is_active = 1;

        if ($request->isPost && $request->post('action_type') === 'quick_add') {
            if ($quickQuestion->load($request->post()) && $quickQuestion->save()) {
                $maxOrder = (int) ExamQuestionMap::find()->where(['exam_id' => $exam->id])->max('sort_order');
                $map = new ExamQuestionMap([
                    'exam_id' => $exam->id,
                    'question_id' => $quickQuestion->id,
                    'sort_order' => $maxOrder + 1,
                ]);
                $map->save(false);
                Yii::$app->session->setFlash('success', 'New question created and added to this exam.');
                return $this->redirect(['questions', 'id' => $exam->id]);
            }
        }

        // Handle saving selected question IDs from bank
        if ($request->isPost && $request->post('action_type') === 'sync_bank') {
            $selectedIds = array_unique(array_map('intval', (array) $request->post('question_ids', [])));
            $tx = Yii::$app->db->beginTransaction();
            try {
                ExamQuestionMap::deleteAll(['exam_id' => $exam->id]);
                $order = 1;
                foreach ($selectedIds as $qid) {
                    if ($qid > 0) {
                        $map = new ExamQuestionMap([
                            'exam_id' => $exam->id,
                            'question_id' => $qid,
                            'sort_order' => $order++,
                        ]);
                        $map->save(false);
                    }
                }
                $tx->commit();
                Yii::$app->session->setFlash('success', count($selectedIds) . ' question(s) assigned to this exam.');
                return $this->redirect(['view', 'id' => $exam->id]);
            } catch (\Throwable $e) {
                $tx->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to update exam questions.');
            }
        }

        $filterCourse = $request->get('course_id', $exam->course_id);
        $filterSubject = $request->get('subject_id', $exam->subject_id);
        $filterDifficulty = $request->get('difficulty', '');
        $showAll = (bool) $request->get('show_all', false);

        $bankQuery = ExamQuestion::find()
            ->with(['course', 'subject'])
            ->where(['is_active' => 1])
            ->orderBy(['id' => SORT_DESC]);

        if (!$showAll) {
            if ($filterCourse !== null && $filterCourse !== '') {
                $bankQuery->andWhere(['or', ['course_id' => (int) $filterCourse], ['course_id' => null]]);
            }
            if ($filterSubject !== null && $filterSubject !== '') {
                $bankQuery->andWhere(['or', ['subject_id' => (int) $filterSubject], ['subject_id' => null]]);
            }
        }
        if ($filterDifficulty !== '') {
            $bankQuery->andWhere(['difficulty' => $filterDifficulty]);
        }

        $assignedIds = array_map('intval', ExamQuestionMap::find()
            ->select('question_id')
            ->where(['exam_id' => $exam->id])
            ->column());

        return $this->render('questions', [
            'exam' => $exam,
            'bankQuestions' => $bankQuery->all(),
            'assignedIds' => $assignedIds,
            'quickQuestion' => $quickQuestion,
            'lists' => $this->module->resolveAcademicLists(),
            'filters' => [
                'course_id' => $filterCourse,
                'subject_id' => $filterSubject,
                'difficulty' => $filterDifficulty,
                'show_all' => $showAll,
            ],
        ]);
    }

    public function actionResults($id)
    {
        $exam = $this->findModel($id);

        $attempts = ExamAttempt::find()
            ->with(['user', 'student.info'])
            ->where(['exam_id' => $exam->id])
            ->orderBy(['submitted_at' => SORT_DESC, 'started_at' => SORT_DESC])
            ->all();

        $submitted = array_filter($attempts, function ($a) {
            return $a->status === ExamAttempt::STATUS_SUBMITTED;
        });
        $avgPct = count($submitted) > 0
            ? round(array_sum(array_map(function ($a) { return (float) $a->percentage; }, $submitted)) / count($submitted), 2)
            : 0;
        $passedCount = count(array_filter($submitted, function ($a) { return (int) $a->is_passed === 1; }));

        return $this->render('results', [
            'exam' => $exam,
            'attempts' => $attempts,
            'stats' => [
                'total' => count($attempts),
                'submitted' => count($submitted),
                'avg_percentage' => $avgPct,
                'passed' => $passedCount,
            ],
        ]);
    }

    public function actionAttemptView($attempt_id)
    {
        $attempt = ExamAttempt::find()
            ->with(['exam', 'user', 'student.info'])
            ->where(['id' => (int) $attempt_id])
            ->one();

        if (!$attempt) {
            throw new NotFoundHttpException('Attempt not found.');
        }

        $questionIds = $attempt->getOrderedQuestionIds();
        $questions = ExamQuestion::find()->where(['id' => $questionIds])->indexBy('id')->all();
        $answers = ExamAttemptAnswer::find()->where(['attempt_id' => $attempt->id])->indexBy('question_id')->all();

        return $this->render('attempt-view', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'questionIds' => $questionIds,
            'questions' => $questions,
            'answers' => $answers,
        ]);
    }

    public function actionExportResults($id)
    {
        $exam = $this->findModel($id);
        $attempts = ExamAttempt::find()
            ->with(['user', 'student.info'])
            ->where(['exam_id' => $exam->id])
            ->orderBy(['submitted_at' => SORT_DESC])
            ->all();

        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Student', 'Username', 'Status', 'Started At', 'Submitted At', 'Duration', 'Answered', 'Correct', 'Obtained Marks', 'Total Marks', 'Percentage', 'Result']);

        foreach ($attempts as $a) {
            $passLabel = $a->is_passed === null ? 'N/A' : ($a->is_passed ? 'PASS' : 'FAIL');
            fputcsv($out, [
                $a->getStudentDisplayName(),
                $a->user ? $a->user->username : '',
                ucfirst($a->status),
                $a->started_at,
                $a->submitted_at,
                $a->getFormattedDuration(),
                $a->answered_count . '/' . $a->total_questions,
                $a->correct_count,
                $a->obtained_marks,
                $a->total_marks,
                $a->percentage . '%',
                $a->status === ExamAttempt::STATUS_SUBMITTED ? $passLabel : 'In Progress',
            ]);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return Yii::$app->response->sendContentAsFile($csv, 'exam_' . $exam->id . '_results_' . date('Ymd_His') . '.csv', [
            'mimeType' => 'text/csv',
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $attemptIds = ExamAttempt::find()->select('id')->where(['exam_id' => $model->id])->column();
        if (!empty($attemptIds)) {
            ExamAttemptAnswer::deleteAll(['attempt_id' => $attemptIds]);
            ExamAttempt::deleteAll(['exam_id' => $model->id]);
        }
        ExamQuestionMap::deleteAll(['exam_id' => $model->id]);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Exam deleted.');
        return $this->redirect(['index']);
    }

    protected function findModel($id): Exam
    {
        $model = Exam::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('The requested exam does not exist.');
        }
        return $model;
    }
}
