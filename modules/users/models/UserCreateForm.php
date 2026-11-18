<?php

namespace app\modules\users\models;

use Yii;
use yii\base\Model;
use app\models\User;

class UserCreateForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $role;

    public function rules()
    {
        return [
            [['username', 'password', 'role'], 'required'],
            [['username', 'email', 'role'], 'string', 'max' => 150],
            ['password', 'string', 'min' => 6],
            ['email', 'email'],
            ['username', 'validateUsernameUnique'],
        ];
    }

    public function validateUsernameUnique($attribute, $params)
    {
        if (User::find()->where(['username' => $this->username])->exists()) {
            $this->addError($attribute, 'This username is already taken.');
        }
    }

    public function createUser()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->password_hash = Yii::$app->security->generatePasswordHash($this->password);
            $user->status = 10;
            $user->created_at = time();
            $user->updated_at = time();

            if (!$user->save(false)) {
                throw new \Exception('User not saved.');
            }

            $auth = Yii::$app->authManager;
            $role = $auth->getRole($this->role);

            if (!$role) {
                throw new \Exception('Selected role does not exist.');
            }

            $auth->assign($role, $user->id);

            $transaction->commit();
            return $user;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
