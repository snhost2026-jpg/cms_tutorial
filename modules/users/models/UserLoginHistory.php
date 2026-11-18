<?php

namespace app\modules\users\models;

use yii\db\ActiveRecord;
use app\models\User;

class UserLoginHistory extends ActiveRecord
{
    public static function tableName()
    {
        return 'user_login_history';
    }

    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'is_active'], 'integer'],
            [['login_at', 'logout_at', 'created_at', 'updated_at'], 'safe'],
            [['user_agent'], 'string'],
            [['ip_address'], 'string', 'max' => 50],
            [['location', 'session_id', 'logout_reason'], 'string', 'max' => 255],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
