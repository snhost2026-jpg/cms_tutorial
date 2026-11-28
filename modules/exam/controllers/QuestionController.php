<?php

namespace app\modules\exam\controllers;

use app\modules\exam\models\ExamQuestion;
use app\modules\exam\models\ExamQuestionMap;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class QuestionController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $courseId = $request->get('course_id');
        $subjectId = $request->get('subject_id');
        $difficulty = $request->get('difficulty');
        $q = trim((string) $request->get('q', ''));

        $query = ExamQuestion::find()
            ->with(['course', 'subject'])
            ->orderBy(['id' => SORT_DESC]);

        if ($courseId !== null && $courseId !== '') {
            $query->andWhere(['course_id' => (int) $courseId]);
        }
        if ($subjectId !== null && $subjectId !== '') {
            $query->andWhere(['subject_id' => (int) $subjectId]);
        }
        if ($difficulty !== null && $difficulty !== '') {
            $query->andWhere(['difficulty' => $difficulty]);
        }
        if ($q !== '') {
            $query->andWhere(['like', 'question_text', $q]);
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
                'subject_id' => $subjectId,
                'difficulty' => $difficulty,
                'q' => $q,
            ],
        ]);
    }

    public function actionCreate()
    {
        $model = new ExamQuestion();
        $model->marks = 1;
        $model->difficulty = ExamQuestion::DIFFICULTY_MEDIUM;
        $model->is_active = 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Question added to the Question Bank.');
            if (Yii::$app->request->post('save_and_add')) {
                return $this->redirect(['create', 'course_id' => $model->course_id, 'subject_id' => $model->subject_id]);
            }
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isGet) {
            if (Yii::$app->request->get('course_id')) {
                $model->course_id = (int) Yii::$app->request->get('course_id');
            }
            if (Yii::$app->request->get('subject_id')) {
                $model->subject_id = (int) Yii::$app->request->get('subject_id');
            }
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
            Yii::$app->session->setFlash('success', 'Question updated successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'lists' => $this->module->resolveAcademicLists(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        ExamQuestionMap::deleteAll(['question_id' => $model->id]);
        $model->delete();
        Yii::$app->session->setFlash('success', 'Question deleted.');
        return $this->redirect(['index']);
    }

    protected function findModel($id): ExamQuestion
    {
        $model = ExamQuestion::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('The requested question does not exist.');
        }
        return $model;
    }
}
