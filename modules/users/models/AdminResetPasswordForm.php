<?php

namespace app\modules\users\models;

use Yii;
use yii\base\Model;
use app\models\User;

class AdminResetPasswordForm extends Model
{
    public $new_password;
    public $confirm_password;

    public function rules()
    {
        return [
            [['new_password', 'confirm_password'], 'required'],
            ['new_password', 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'message' => 'Confirm password must match new password.'],
        ];
    }

    public function resetForUser(User $user)
    {
        if (!$this->validate()) {
            return false;
        }

        $user->password_hash = Yii::$app->security->generatePasswordHash($this->new_password);
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->updated_at = time();

        return $user->save(false, ['password_hash', 'auth_key', 'updated_at']);
    }
}
