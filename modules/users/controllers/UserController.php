<?php

namespace app\modules\users\controllers;

use Yii;
use app\models\User;
use app\modules\users\models\UserCreateForm;
use app\modules\users\models\UserUpdateForm;
use app\modules\users\models\AdminResetPasswordForm;
use app\modules\users\models\UserLoginHistory;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'create', 'update', 'check-username', 'reset-password', 'toggle-status', 'login-history'],
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
        if (Yii::$app->user->isGuest) {
            return parent::beforeAction($action);
        }

        $hasRouteAccess = \app\components\AccessHelper::checkRoute('/' . $action->getUniqueId())
            || \app\components\AccessHelper::checkRoute('/users/user/*')
            || \app\components\AccessHelper::checkRoute('/users/*')
            || \app\components\AccessHelper::checkRoute('/user/*');

        if (!Yii::$app->user->can('manageRbac') && !Yii::$app->user->can('manageEmployee') && !$hasRouteAccess) {
            throw new ForbiddenHttpException('You are not allowed to access user management.');
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $users = User::find()->orderBy(['id' => SORT_DESC])->all();
        return $this->render('index', ['users' => $users]);
    }

    public function actionCreate()
    {
        $model = new UserCreateForm();

        if ($model->load(Yii::$app->request->post())) {
            $plainPassword = $model->password;

            if ($user = $model->createUser()) {
                $siteLink = Yii::$app->request->hostInfo;
                $smtpHost = getenv('SMTP_HOST') ?: (Yii::$app->params['smtpHost'] ?? 'smtp.gmail.com');
                $smtpUser = getenv('SMTP_USER') ?: (Yii::$app->params['smtpUser'] ?? 'crm.sargnexus@gmail.com');
                $smtpPass = getenv('SMTP_PASS') ?: (Yii::$app->params['smtpPass'] ?? 'ilygfcstsaxwonyp');
                $smtpPort = getenv('SMTP_PORT') ?: (Yii::$app->params['smtpPort'] ?? 587);

                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = $smtpHost;
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtpUser;
                    $mail->Password = $smtpPass;
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = (int)$smtpPort;

                    $mail->setFrom($smtpUser, (Yii::$app->name ?? 'SARGNEXUS CMS'));
                    $mail->addAddress($user->email, $user->username);
                    $mail->isHTML(true);
                    $mail->Subject = 'Your CMS Login Details';
                    $mail->Body = '
<div style="font-family:Segoe UI,Arial,sans-serif; background:#f4f6f9; padding:30px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);">

        <!-- HEADER -->
        <div style="background:#0d6efd; color:#ffffff; padding:20px 25px;">
            <h2 style="margin:0; font-size:20px;">SARGNEXUS CRM</h2>
            <div style="font-size:13px; opacity:0.9;">Enterprise Workspace Access</div>
        </div>

        <!-- BODY -->
        <div style="padding:25px; color:#333333;">

            <p style="margin-bottom:15px;">Dear <strong>' . htmlspecialchars($user->username) . '</strong>,</p>

            <p style="margin-bottom:20px;">
                Your account has been successfully created in the SARGNEXUS CRM system.
                Please find your login credentials below.
            </p>

            <!-- LOGIN BOX -->
            <div style="background:#f8f9fa; border:1px solid #e3e6ea; border-radius:8px; padding:18px; margin-bottom:20px;">
                
                <div style="margin-bottom:10px;">
                    <strong>Website:</strong><br>
                    <a href="' . $siteLink . '" style="color:#0d6efd; text-decoration:none;">
                        ' . $siteLink . '
                    </a>
                </div>

                <div style="margin-bottom:10px;">
                    <strong>Username:</strong><br>
                    ' . htmlspecialchars($user->username) . '
                </div>

                <div>
                    <strong>Password:</strong><br>
                    ' . htmlspecialchars($plainPassword) . '
                </div>

            </div>

            <!-- NOTE -->
            <div style="background:#fff3cd; border:1px solid #ffeeba; padding:12px; border-radius:6px; font-size:13px;">
                For security reasons, please change your password after your first login.
            </div>

            <p style="margin-top:20px;">
                If you have any queries, please contact the HR team.
            </p>

            <p style="margin-top:25px;">
                Regards,<br>
                <strong>SARGNEXUS Team</strong>
            </p>

        </div>

        <!-- FOOTER -->
        <div style="background:#f1f3f5; padding:15px 25px; font-size:12px; color:#6c757d;">
            This is an automated email. Please do not reply directly.
        </div>

    </div>
</div>
';
                    $mail->send();

                    Yii::$app->session->setFlash('success', 'User created successfully and email sent.');
                } catch (\Throwable $e) {
                    Yii::$app->session->setFlash('warning', 'User created successfully, but mail send failed: ' . $e->getMessage());
                }

                return $this->redirect(['index']);
            }
        }

        $roles = Yii::$app->authManager->getRoles();
        return $this->render('create', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }

    public function actionUpdate($id)
    {
        $user = $this->findUser($id);
        $model = new UserUpdateForm($user);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                if (Yii::$app->request->isAjax) {
                    return $this->asJson([
                        'success' => true,
                        'message' => 'Username updated successfully.',
                        'user' => [
                            'id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email,
                        ],
                    ]);
                }
                Yii::$app->session->setFlash('success', 'Username updated successfully.');
                return $this->redirect(['index']);
            }

            if (Yii::$app->request->isAjax) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $model->getErrors(),
                    'message' => $model->getFirstError('username') ?: 'Failed to update username.',
                ]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    public function actionCheckUsername($username, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $username = trim((string)$username);

        if ($username === '') {
            return [
                'available' => false,
                'message' => 'Username cannot be blank.',
            ];
        }

        $query = User::find()->where(['username' => $username]);
        if ($id) {
            $query->andWhere(['!=', 'id', (int)$id]);
        }

        if ($query->exists()) {
            return [
                'available' => false,
                'message' => 'Username is already taken.',
            ];
        }

        return [
            'available' => true,
            'message' => 'Username is available.',
        ];
    }

    public function actionResetPassword($id)
    {
        $user = $this->findUser($id);
        $model = new AdminResetPasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->resetForUser($user)) {
            Yii::$app->session->setFlash('success', 'Password reset successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('reset-password', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    public function actionToggleStatus($id)
    {
        $user = $this->findUser($id);
        $user->status = ((int)$user->status === 10) ? 0 : 10;
        $user->updated_at = time();
        $user->save(false, ['status', 'updated_at']);

        Yii::$app->session->setFlash('success', 'User status updated successfully.');
        return $this->redirect(['index']);
    }

    protected function findUser($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }
        return $user;
    }

    public function actionLoginHistory()
    {
        $hasRouteAccess = \app\components\AccessHelper::checkRoute('/users/user/login-history')
            || \app\components\AccessHelper::checkRoute('/user/login-history')
            || \app\components\AccessHelper::checkRoute('/users/*');

        if (!Yii::$app->user->can('manageRbac') && !Yii::$app->user->can('manageEmployee') && !$hasRouteAccess) {
            throw new ForbiddenHttpException('You are not allowed to access login history.');
        }

        $history = UserLoginHistory::find()
            ->with('user')
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('login-history', [
            'history' => $history,
        ]);
    }

    public static function getUserRole($userId)
    {
        $roles = Yii::$app->authManager->getRolesByUser($userId);
        return !empty($roles) ? implode(', ', array_keys($roles)) : 'Not Assigned';
    }
}
