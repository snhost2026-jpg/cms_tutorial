<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use app\models\LoginForm;
use app\models\ChangePasswordForm;
use app\models\ContactForm;
use app\modules\users\models\UserLoginHistory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'change-password'],
                'rules' => [
                    [
                        'actions' => ['logout', 'change-password'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post', 'get'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        return $this->render('index');
    }

    /**
     * Change password for logged in user.
     */
    public function actionChangePassword()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new ChangePasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Yii::$app->session->setFlash('success', 'Password changed successfully.');
            return $this->refresh();
        }

        return $this->render('change-password', [
            'model' => $model,
        ]);
    }

    /**
     * Login action with 2-step credentials and OTP verification.
     */
    public function actionLogin()
    {
        $this->layout = 'login';

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        $showOtpField = false;

        // Step 1: Username + password submit
        if (Yii::$app->request->isPost && Yii::$app->request->post('login_step') === 'credentials') {
            $model->scenario = 'login';

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $user = $model->getUser();

                $requireOtp = Yii::$app->params['enableLoginOtp'] ?? false;

                // If OTP is required and user has email configured
                if ($requireOtp && $user && !empty($user->email)) {
                    $otp = (string) random_int(100000, 999999);
                    $user->setOtp($otp);

                    if ($user->save(false, ['otp_code', 'otp_expiry', 'otp_verified'])) {
                        Yii::$app->session->set('otp_user_id', $user->id);
                        Yii::$app->session->set('otp_username', $model->username);
                        Yii::$app->session->set('otp_rememberMe', $model->rememberMe);

                        // Attempt to send OTP via PHPMailer
                        try {
                            $smtpHost = getenv('SMTP_HOST') ?: (Yii::$app->params['smtpHost'] ?? 'smtp.gmail.com');
                            $smtpUser = getenv('SMTP_USER') ?: (Yii::$app->params['smtpUser'] ?? '');
                            $smtpPass = getenv('SMTP_PASS') ?: (Yii::$app->params['smtpPass'] ?? '');
                            $smtpPort = getenv('SMTP_PORT') ?: (Yii::$app->params['smtpPort'] ?? 587);

                            if (!empty($smtpUser) && !empty($smtpPass)) {
                                $mail = new PHPMailer(true);
                                $mail->isSMTP();
                                $mail->Host = $smtpHost;
                                $mail->SMTPAuth = true;
                                $mail->Username = $smtpUser;
                                $mail->Password = $smtpPass;
                                $mail->SMTPSecure = 'tls';
                                $mail->Port = (int)$smtpPort;
                                $mail->setFrom($smtpUser, (Yii::$app->name ?? 'CMS') . ' Auth');
                                $mail->addAddress($user->email);
                                $mail->isHTML(true);
                                $mail->Subject = 'Your Login OTP - ' . (Yii::$app->name ?? 'CMS');
                                $mail->Body = "<div style='font-family:Arial,sans-serif;padding:20px;'><p>Hello <b>" . htmlspecialchars($user->username) . "</b>,</p><p>Your verification code is: <h2>{$otp}</h2></p><p>Valid for 5 minutes.</p></div>";
                                $mail->send();
                            } else {
                                // In local dev without SMTP credentials, display OTP as info flash
                                Yii::$app->session->setFlash('info', 'Development mode: Your OTP is <strong>' . $otp . '</strong>');
                            }

                            $otpModel = new LoginForm();
                            $otpModel->username = $model->username;
                            $otpModel->rememberMe = $model->rememberMe;
                            $otpModel->otp_stage = 1;

                            return $this->render('login', [
                                'model' => $otpModel,
                                'showOtpField' => true,
                            ]);
                        } catch (\Exception $e) {
                            Yii::$app->session->setFlash('error', 'OTP email could not be sent: ' . $e->getMessage());
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Unable to generate OTP.');
                    }
                } else {
                    // Direct login (when OTP disabled or no email)
                    return $this->completeLogin($user, $model->rememberMe);
                }
            }

            return $this->render('login', [
                'model' => $model,
                'showOtpField' => false,
            ]);
        }

        // Step 2: OTP verify
        if (Yii::$app->request->isPost && Yii::$app->request->post('login_step') === 'otp') {
            $model->scenario = 'otp';
            $model->load(Yii::$app->request->post());

            $userId = Yii::$app->session->get('otp_user_id');
            $username = Yii::$app->session->get('otp_username');
            $rememberMe = Yii::$app->session->get('otp_rememberMe', false);

            if ($userId) {
                $user = \app\models\User::findOne($userId);

                if ($user) {
                    $model->username = $username;
                    $model->rememberMe = $rememberMe;

                    if ($user->validateOtp($model->otp)) {
                        $user->clearOtp();
                        $user->save(false, ['otp_code', 'otp_expiry', 'otp_verified']);

                        // clear temp session
                        Yii::$app->session->remove('otp_user_id');
                        Yii::$app->session->remove('otp_username');
                        Yii::$app->session->remove('otp_rememberMe');

                        return $this->completeLogin($user, $rememberMe);
                    } else {
                        $model->addError('otp', 'Invalid or expired OTP.');
                    }
                }
            }

            return $this->render('login', [
                'model' => $model,
                'showOtpField' => true,
            ]);
        }

        return $this->render('login', [
            'model' => $model,
            'showOtpField' => false,
        ]);
    }

    /**
     * Completes user login and records login history.
     */
    protected function completeLogin($user, $rememberMe = false)
    {
        Yii::$app->user->login($user, $rememberMe ? 3600 * 24 * 30 : 0);

        // Record login history
        try {
            $history = new UserLoginHistory();
            $history->user_id = $user->id;
            $history->login_at = date('Y-m-d H:i:s');
            $history->ip_address = Yii::$app->request->userIP;
            $history->location = 'Unknown';
            $history->user_agent = Yii::$app->request->userAgent;
            $history->is_active = 1;
            $history->session_id = Yii::$app->session->id;
            $history->created_at = date('Y-m-d H:i:s');
            $history->updated_at = date('Y-m-d H:i:s');
            $history->save(false);

            Yii::$app->session->set('login_history_id', $history->id);
            Yii::$app->session->set('last_activity_time', time());
        } catch (\Throwable $e) {
            // Table might not be migrated yet
        }

        if (Yii::$app->user->can('manageRbac') || Yii::$app->user->can('admin') || Yii::$app->user->can('SuperAdmin')) {
            return $this->redirect(['/rights/assignment']);
        }

        return $this->redirect(['/site/index']);
    }

    /**
     * Logout action.
     */
    public function actionLogout()
    {
        $historyId = Yii::$app->session->get('login_history_id');

        if (!empty($historyId)) {
            try {
                $history = UserLoginHistory::findOne($historyId);
                if ($history && (int)$history->is_active === 1) {
                    $history->logout_at = date('Y-m-d H:i:s');
                    $history->is_active = 0;
                    $history->logout_reason = 'manual_logout';
                    $history->updated_at = date('Y-m-d H:i:s');
                    $history->save(false, ['logout_at', 'is_active', 'logout_reason', 'updated_at']);
                }
            } catch (\Throwable $e) {
                // Ignore if table issue
            }
        }

        Yii::$app->user->logout();
        return $this->redirect(['site/login']);
    }

    /**
     * Displays about page.
     */
    public function actionAbout(): string
    {
        return $this->render('about');
    }

    /**
     * Displays contact page.
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }
}
