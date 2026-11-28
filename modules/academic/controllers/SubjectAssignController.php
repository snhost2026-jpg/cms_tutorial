<?php

namespace app\modules\academic\controllers;

use Yii;
use app\components\AccessHelper;
use app\modules\academic\models\SubjectAssign;
use app\modules\course\models\Batch;
use app\modules\course\models\Course;
use app\modules\course\models\Section;
use app\modules\course\models\Subject;
use app\modules\employee\models\Employee;
use app\modules\employee\models\EmployeeInfo;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class SubjectAssignController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'delete'],
                'rules' => [['allow' => true, 'roles' => ['@']]],
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
            && !Yii::$app->user->can('manageRbac')
            && !AccessHelper::checkRoute('/academic/subject-assign/*')
            && !AccessHelper::checkRoute('/academic/*')
        ) {
            throw new ForbiddenHttpException('You are not allowed to assign subjects.');
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $model = new SubjectAssign();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Subject assigned to the teacher successfully.');
            return $this->redirect(['index']);
        }

        $query = SubjectAssign::find()->with(['course', 'batch', 'section', 'employee.info']);

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 25],
                'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            ]),
            'courseOptions' => $this->courseOptions(),
            'batchOptions' => $this->batchOptions(),
            'sectionOptions' => $this->sectionOptions(),
            'subjectOptions' => $this->subjectOptions(),
            'teacherOptions' => $this->teacherOptions(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = SubjectAssign::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('Subject assignment not found.');
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Subject assignment removed successfully.');
        return $this->redirect(['index']);
    }

    private function courseOptions()
    {
        $options = [];
        foreach (Course::find()->orderBy(['name' => SORT_ASC])->all() as $course) {
            $options[$course->id] = $course->name;
        }
        return $options;
    }

    private function batchOptions()
    {
        $options = [];
        foreach (Batch::find()->with('course')->orderBy(['name' => SORT_ASC])->all() as $batch) {
            $label = $batch->name;
            if ($batch->course) {
                $label .= ' (' . $batch->course->name . ')';
            }
            $options[$batch->id] = $label;
        }
        return $options;
    }

    private function sectionOptions()
    {
        $options = [];
        foreach (Section::find()->with(['course', 'batch'])->orderBy(['name' => SORT_ASC])->all() as $section) {
            $parts = array_filter([
                $section->course ? $section->course->name : null,
                $section->batch ? $section->batch->name : null,
                $section->name,
            ]);
            $options[$section->id] = implode(' / ', $parts);
        }
        return $options;
    }

    private function subjectOptions()
    {
        $options = [];
        foreach (Subject::find()->with('course')->orderBy(['name' => SORT_ASC])->all() as $subject) {
            $label = $subject->name;
            if ($subject->code) {
                $label .= ' (' . $subject->code . ')';
            }
            $options[$subject->code ?: (string) $subject->id] = $label;
        }
        return $options;
    }

    private function teacherOptions()
    {
        $options = [];
        $employees = Employee::find()
            ->joinWith('info')
            ->where([
                'emp_master.is_status' => Employee::STATUS_ACTIVE,
                'emp_info.employee_type' => EmployeeInfo::TYPE_TEACHING,
            ])
            ->all();

        foreach ($employees as $employee) {
            $label = $employee->info ? $employee->info->fullName : 'Employee #' . $employee->emp_master_id;
            if ($employee->info && $employee->info->employee_code) {
                $label .= ' (' . $employee->info->employee_code . ')';
            }
            $options[$employee->emp_master_id] = $label;
        }
        return $options;
    }
}
