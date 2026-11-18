<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ChangePasswordForm extends Model
{
    public $current_password;
    public $new_password;
    public $confirm_password;

    public function rules()
    {
        return [
            [['current_password', 'new_password', 'confirm_password'], 'required'],
            ['current_password', 'validateCurrentPassword'],
            ['new_password', 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'message' => 'Confirm password must match new password.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm New Password',
        ];
    }

    public function validateCurrentPassword($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = Yii::$app->user->identity;

        if (!$user || !Yii::$app->security->validatePassword($this->current_password, $user->password_hash)) {
            $this->addError($attribute, 'Current password is incorrect.');
        }
    }

    public function changePassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = Yii::$app->user->identity;
        $user->password_hash = Yii::$app->security->generatePasswordHash($this->new_password);
        $user->updated_at = time();

        return $user->save(false, ['password_hash', 'updated_at']);
    }
}