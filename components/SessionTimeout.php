<?php

namespace app\components;

use Yii;
use yii\base\Behavior;
use yii\web\Application;
use app\modules\users\models\UserLoginHistory;

class SessionTimeout extends Behavior
{
    public $timeout = 3600; // 1 hour

    public function events()
    {
        return [
            Application::EVENT_BEFORE_REQUEST => 'checkTimeout',
        ];
    }

    public function checkTimeout()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $lastActivity = Yii::$app->session->get('last_activity_time');

        if ($lastActivity && (time() - $lastActivity > $this->timeout)) {
            $historyId = Yii::$app->session->get('login_history_id');

            if ($historyId) {
                try {
                    $history = UserLoginHistory::findOne($historyId);
                    if ($history && (int)$history->is_active === 1) {
                        $history->logout_at = date('Y-m-d H:i:s');
                        $history->is_active = 0;
                        $history->logout_reason = 'inactive_timeout';
                        $history->updated_at = date('Y-m-d H:i:s');
                        $history->save(false, ['logout_at', 'is_active', 'logout_reason', 'updated_at']);
                    }
                } catch (\Throwable $e) {
                    // Ignore if history table not available
                }
            }

            Yii::$app->user->logout();
            Yii::$app->session->destroy();
            Yii::$app->response->redirect(['site/login'])->send();
            Yii::$app->end();
        }

        Yii::$app->session->set('last_activity_time', time());
    }
}
