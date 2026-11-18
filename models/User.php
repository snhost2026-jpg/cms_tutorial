<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'user';
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => 10]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => 10]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
public function setOtp($otp)
{
    $this->otp_code = $otp;
    $this->otp_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $this->otp_verified = 0;
}

public function validateOtp($otp)
{
    if (empty($this->otp_code) || empty($this->otp_expiry)) {
        return false;
    }

    if ((string)$this->otp_code !== (string)$otp) {
        return false;
    }

    if (strtotime($this->otp_expiry) < time()) {
        return false;
    }

    return true;
}

public function clearOtp()
{
    $this->otp_code = null;
    $this->otp_expiry = null;
    $this->otp_verified = 1;
}
}