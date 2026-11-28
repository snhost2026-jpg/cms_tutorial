<?php

namespace app\modules\employee\controllers;

use Yii;
use app\components\AccessHelper;
use app\models\User;
use app\modules\employee\models\Employee;
use app\modules\employee\models\EmployeeInfo;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class EmployeeController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        $actionRoute = '/employee/' . $action->id;
        $moduleRoute = '/' . $action->getUniqueId();
        if (
            !Yii::$app->user->isGuest
            && !Yii::$app->user->can('SuperAdmin')
            && !Yii::$app->user->can('admin')
            && !Yii::$app->user->can('manageEmployee')
            && !Yii::$app->user->can('manageRbac')
            && !AccessHelper::checkRoute($actionRoute)
            && !AccessHelper::checkRoute($moduleRoute)
            && !AccessHelper::checkRoute('/employee/*')
        ) {
            throw new ForbiddenHttpException('You are not allowed to access employee management.');
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $query = Employee::find()
            ->joinWith('info')
            ->orderBy(['emp_first_name' => SORT_ASC, 'emp_last_name' => SORT_ASC]);
        $search = $request->get('q', '');
        $search = is_string($search) ? trim($search) : '';
        $type = $request->get('employee_type');
        $status = $request->get('status');
        $type = is_string($type) ? $type : null;
        $status = is_string($status) ? $status : null;

        if ($search !== '') {
            $conditions = [
                'or',
                ['like', 'employee_code', $search],
                ['like', 'emp_first_name', $search],
                ['like', 'emp_middle_name', $search],
                ['like', 'emp_last_name', $search],
                ['like', 'emp_email_id', $search],
                ['like', 'emp_mobile_no', $search],
            ];
            if (ctype_digit($search)) {
                $conditions[] = ['emp_master_id' => (int) $search];
            }
            $query->andWhere($conditions);
        }
        if (isset(EmployeeInfo::getTypeOptions()[$type])) {
            $query->andWhere(['employee_type' => $type]);
        }
        if ($status === '0' || $status === '1') {
            $query->andWhere(['is_status' => (int) $status]);
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 25],
            ]),
            'filters' => [
                'q' => $search,
                'employee_type' => $type,
                'status' => $status,
            ],
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Employee();
        $info = new EmployeeInfo();

        if ($model->load(Yii::$app->request->post()) && $info->load(Yii::$app->request->post())) {
            $valid = $info->validate();
            $valid = $model->validate() && $valid;

            if ($valid) {
                $this->saveEmployee($model, $info);
                Yii::$app->session->setFlash(
                    'success',
                    'Employee created successfully. Login ID and initial password are the employee code; change the password after first login.'
                );
                return $this->redirect(['view', 'id' => $model->emp_master_id]);
            }
        }

        return $this->render('create', ['model' => $model, 'info' => $info]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $info = $model->info;
        $isNewInfo = $info === null;
        if ($isNewInfo) {
            $info = new EmployeeInfo();
        }

        if ($model->load(Yii::$app->request->post()) && $info->load(Yii::$app->request->post())) {
            $valid = $info->validate();
            $valid = $model->validate() && $valid;

            if ($valid) {
                $this->saveEmployee($model, $info, $isNewInfo);
                Yii::$app->session->setFlash('success', 'Employee updated successfully.');
                return $this->redirect(['view', 'id' => $model->emp_master_id]);
            }
        }

        return $this->render('update', ['model' => $model, 'info' => $info]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->info !== null && $model->info->delete() === false) {
                throw new \RuntimeException('Employee information could not be deleted.');
            }
            if ($model->delete() === false) {
                throw new \RuntimeException('Employee record could not be deleted.');
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', 'Employee deleted successfully.');
        return $this->redirect(['index']);
    }

    protected function saveEmployee(Employee $model, EmployeeInfo $info, $isNewInfo = true)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$info->save(false)) {
                throw new \RuntimeException('Employee information could not be saved.');
            }
            if ($isNewInfo) {
                $model->emp_master_emp_info_id = $info->emp_info_id;
            }
            if ($model->isNewRecord) {
                $model->emp_master_user_id = $this->createLoginAccount($info);
            }
            if (!$model->save(false)) {
                throw new \RuntimeException('Employee record could not be saved.');
            }

            if ($isNewInfo || (int) $info->emp_info_emp_master_id !== (int) $model->emp_master_id) {
                if (!$info->updateAttributes(['emp_info_emp_master_id' => $model->emp_master_id])) {
                    throw new \RuntimeException('Employee information could not be linked to its master record.');
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    protected function createLoginAccount(EmployeeInfo $info)
    {
        $user = new User();
        $user->username = $info->employee_code;
        $user->email = $info->emp_email_id ?: '';
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->password_hash = Yii::$app->security->generatePasswordHash($info->employee_code);
        $user->status = 10;
        $user->created_at = time();
        $user->updated_at = time();
        if (!$user->save(false)) {
            throw new \RuntimeException('Employee login account could not be created.');
        }

        $authManager = Yii::$app->authManager;
        $role = $authManager->getRole('employee');
        if ($role === null) {
            $role = $authManager->createRole('employee');
            $role->description = 'Employee login account';
            $authManager->add($role);
        }
        $authManager->assign($role, $user->id);

        return $user->id;
    }

    protected function findModel($id)
    {
        $model = Employee::findOne($id);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested employee does not exist.');
    }
}
