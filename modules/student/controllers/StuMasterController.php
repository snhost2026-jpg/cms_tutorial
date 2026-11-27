<?php

namespace app\modules\student\controllers;

use Yii;
use app\modules\student\models\StuInfo;
use app\modules\student\models\StuMaster;
use app\modules\student\models\StuMasterSearch;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CRUD for students: list, view, create, update, soft delete, trash/restore
 * and CSV export. Each student is one stu_master row plus one stu_info row.
 */
class StuMasterController extends Controller
{
    public function behaviors()
    {
        return [
            // only logged-in users; tighten with RBAC roles to suit your school
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'restore' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new StuMasterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, false, $this->module->pageSize);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'lists' => $this->classLists(),
        ]);
    }

    public function actionTrash()
    {
        $searchModel = new StuMasterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, true, $this->module->pageSize);

        return $this->render('trash', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'master' => $this->findModel($id),
            'lists' => $this->classLists(),
        ]);
    }

    public function actionCreate()
    {
        $master = new StuMaster();
        $info = new StuInfo();
        $request = Yii::$app->request;

        if ($master->load($request->post()) && $info->load($request->post())) {
            $valid = $info->validate();
            $valid = $master->validate() && $valid;

            if ($valid) {
                $tx = Yii::$app->db->beginTransaction();
                try {
                    // 1. info row first, so the database assigns the id
                    $info->save(false);

                    // registration number: use the typed one, otherwise PREFIX + year + padded id
                    if ($info->stu_unique_id === null) {
                        $info->stu_unique_id = $this->module->registrationPrefix
                            . date('Y') . str_pad($info->stu_info_id, 5, '0', STR_PAD_LEFT);
                    }

                    // 2. optional login account
                    $tempPassword = null;
                    if ($this->module->createLogin) {
                        list($userId, $tempPassword) = $this->createLoginAccount($info);
                        $master->stu_master_user_id = $userId;
                    }

                    // 3. master row, then link both rows to each other
                    $master->stu_master_stu_info_id = $info->stu_info_id;
                    $master->save(false);
                    $info->updateAttributes([
                        'stu_info_stu_master_id' => $master->stu_master_id,
                        'stu_unique_id' => $info->stu_unique_id,
                    ]);

                    $tx->commit();
                } catch (\Throwable $e) {
                    $tx->rollBack();
                    Yii::error($e->getMessage(), __METHOD__);
                    Yii::$app->session->setFlash('error', 'Could not save the student: ' . $e->getMessage());
                    return $this->render('create', [
                        'master' => $master, 'info' => $info, 'lists' => $this->classLists(),
                    ]);
                }

                $msg = 'Student saved. Registration no: ' . $info->stu_unique_id . '.';
                if ($tempPassword !== null) {
                    $msg .= ' Temporary password: ' . $tempPassword . ' (shown only once).';
                }
                Yii::$app->session->setFlash('success', $msg);
                return $this->redirect(['view', 'id' => $master->stu_master_id]);
            }
        }

        return $this->render('create', [
            'master' => $master, 'info' => $info, 'lists' => $this->classLists(),
        ]);
    }

    public function actionUpdate($id)
    {
        $master = $this->findModel($id);
        $info = $master->info;
        $isNewInfo = false;
        if ($info === null) {
            // repair a master row that lost its info row
            $info = new StuInfo();
            $isNewInfo = true;
        }
        $request = Yii::$app->request;

        if ($master->load($request->post()) && $info->load($request->post())) {
            $valid = $info->validate();
            $valid = $master->validate() && $valid;

            if ($valid) {
                $tx = Yii::$app->db->beginTransaction();
                try {
                    $info->save(false);
                    if ($isNewInfo) {
                        $master->stu_master_stu_info_id = $info->stu_info_id;
                    }
                    $master->save(false);
                    if ($isNewInfo || $info->stu_info_stu_master_id != $master->stu_master_id) {
                        $info->updateAttributes(['stu_info_stu_master_id' => $master->stu_master_id]);
                    }
                    $tx->commit();
                } catch (\Throwable $e) {
                    $tx->rollBack();
                    Yii::error($e->getMessage(), __METHOD__);
                    Yii::$app->session->setFlash('error', 'Could not update the student: ' . $e->getMessage());
                    return $this->render('update', [
                        'master' => $master, 'info' => $info, 'lists' => $this->classLists(),
                    ]);
                }

                Yii::$app->session->setFlash('success', 'Student updated.');
                return $this->redirect(['view', 'id' => $master->stu_master_id]);
            }
        }

        return $this->render('update', [
            'master' => $master, 'info' => $info, 'lists' => $this->classLists(),
        ]);
    }

    /** Soft delete: the row stays in the database with is_status = 2. */
    public function actionDelete($id)
    {
        $this->findModel($id)->softDelete();
        Yii::$app->session->setFlash('success', 'Student moved to trash.');
        return $this->redirect(['index']);
    }

    public function actionRestore($id)
    {
        $master = StuMaster::findWithDeleted()
            ->andWhere(['stu_master.stu_master_id' => $id, 'stu_master.is_status' => StuMaster::STATUS_DELETED])
            ->one();
        if ($master === null) {
            throw new NotFoundHttpException('The deleted student was not found.');
        }
        $master->restore();
        Yii::$app->session->setFlash('success', 'Student restored.');
        return $this->redirect(['view', 'id' => $master->stu_master_id]);
    }

    /** CSV of the students matching the current list filters. */
    public function actionExport()
    {
        $search = new StuMasterSearch();
        $provider = $search->search(Yii::$app->request->queryParams, false, 20);
        $query = $provider->query;
        $query->orderBy(['stu_master.stu_master_id' => SORT_ASC]);

        $columns = [
            'Registration No' => 'stu_unique_id', 'Name' => 'fullName', 'Gender' => 'stu_gender',
            'Date of Birth' => 'stu_dob', 'Admission Date' => 'stu_admission_date',
            'Mobile' => 'stu_mobile_no', 'Email' => 'stu_email_id',
            "Father's Name" => 'father_name', "Father's Mobile" => 'father_mobile',
            "Mother's Name" => 'mother_name', "Mother's Mobile" => 'mother_mobile',
            'Guardian' => 'guardian_name', 'Guardian Mobile' => 'guardian_mobile',
            'Address' => 'address_line', 'City' => 'city', 'State' => 'state', 'Pincode' => 'pincode',
            'Previous School' => 'prev_school_name', 'Previous Class' => 'prev_class',
            'Emergency Contact' => 'emergency_contact_name', 'Emergency Phone' => 'emergency_contact_phone',
        ];

        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads accents correctly
        fputcsv($out, array_merge(array_keys($columns), ['Course', 'Batch', 'Section', 'Status']));

        foreach ($query->each(500) as $master) {
            /* @var $master StuMaster */
            $row = [];
            foreach ($columns as $attr) {
                $row[] = $master->info ? $this->csvSafe($master->info->$attr) : '';
            }
            $row[] = $master->stu_master_course_id;
            $row[] = $master->stu_master_batch_id;
            $row[] = $master->stu_master_section_id;
            $row[] = $master->statusLabel;
            fputcsv($out, $row);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return Yii::$app->response->sendContentAsFile($csv, 'students_' . date('Ymd_His') . '.csv', [
            'mimeType' => 'text/csv',
        ]);
    }

    /** Stops spreadsheet formula injection (cells starting with = + - @). */
    protected function csvSafe($value)
    {
        $value = (string) $value;
        if ($value !== '' && strpos('=+-@', $value[0]) !== false) {
            return "'" . $value;
        }
        return $value;
    }

    /**
     * Dropdown data for the class-placement fields, taken from the module
     * setting "classLists". Missing keys give an empty array (number input).
     */
    protected function classLists()
    {
        $result = [];
        foreach (['course', 'batch', 'section'] as $key) {
            $list = isset($this->module->classLists[$key]) ? $this->module->classLists[$key] : [];
            if (is_callable($list)) {
                $list = call_user_func($list);
            }
            $result[$key] = is_array($list) ? $list : [];
        }
        return $result;
    }

    /**
     * Creates a login in the host app's user table and assigns the role.
     * Only runs when the module option createLogin is true.
     *
     * @return array [user id, temporary password]
     */
    protected function createLoginAccount(StuInfo $info)
    {
        $userClass = '\app\models\User';
        $authClass = '\app\models\AuthAssignment';
        if (!class_exists($userClass) || !class_exists($authClass)) {
            throw new \RuntimeException('createLogin is on, but app\models\User or app\models\AuthAssignment was not found.');
        }

        $password = Yii::$app->security->generateRandomString(10);
        $hasher = $this->module->passwordHasher;
        $hash = $hasher ? call_user_func($hasher, $password) : Yii::$app->security->generatePasswordHash($password);

        $user = new $userClass();
        $user->user_login_id = $info->stu_unique_id;
        $user->user_password = $hash;
        $user->user_type = 'S';
        $user->created_by = Yii::$app->user->id;
        $user->created_at = new Expression('NOW()');
        if (!$user->save(false)) {
            throw new \RuntimeException('Could not create the login user.');
        }

        $auth = new $authClass();
        $auth->item_name = $this->module->loginRole;
        $auth->user_id = $user->user_id;
        $auth->created_at = time();
        if (!$auth->save(false)) {
            throw new \RuntimeException('Could not assign the login role.');
        }

        return [$user->user_id, $password];
    }

    protected function findModel($id)
    {
        $model = StuMaster::find()->with('info')->andWhere(['stu_master.stu_master_id' => $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('The requested student does not exist.');
        }
        return $model;
    }
}
