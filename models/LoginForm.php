<?php

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $otp;
    public $rememberMe = false;
    public $otp_stage = 0;

    private $_user = false;

    public function rules()
    {
        return [
            [['username', 'password'], 'required', 'on' => 'login'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword', 'on' => 'login'],

            [['otp'], 'required', 'on' => 'otp'],
            ['otp', 'string', 'length' => 6],
            ['otp', 'validateOtp', 'on' => 'otp'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'password' => 'Password',
            'otp' => 'OTP',
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    public function validateOtp($attribute, $params)
    {
        $user = $this->getUser();

        if (!$user || !$user->validateOtp($this->otp)) {
            $this->addError($attribute, 'Invalid or expired OTP.');
        }
    }

    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login(
                $this->getUser(),
                $this->rememberMe ? 3600 * 24 * 30 : 0
            );
        }

        return false;
    }

    public function getUser()
    {
        if ($this->_user === false && !empty($this->username)) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}