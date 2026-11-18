<?php

namespace app\modules\users\models;

use Yii;
use yii\base\Model;
use app\models\User;

class UserUpdateForm extends Model
{
    public $id;
    public $username;
    public $email;

    /**
     * @var User
     */
    private $_user;

    public function __construct(User $user, $config = [])
    {
        $this->_user = $user;
        $this->id = $user->id;
        $this->username = $user->username;
        $this->email = $user->email;
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['username'], 'required'],
            [['username', 'email'], 'trim'],
            [['username'], 'string', 'min' => 3, 'max' => 150],
            ['email', 'email'],
            ['email', 'string', 'max' => 150],
            ['username', 'validateUsernameUnique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'email' => 'Email',
        ];
    }

    public function validateUsernameUnique($attribute, $params)
    {
        $exists = User::find()
            ->where(['username' => $this->username])
            ->andWhere(['!=', 'id', $this->_user->id])
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'Username is already taken.');
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->_user->username = $this->username;
        if ($this->email !== null) {
            $this->_user->email = $this->email;
        }
        $this->_user->updated_at = time();

        return $this->_user->save(false, ['username', 'email', 'updated_at']);
    }

    public function getUser()
    {
        return $this->_user;
    }
}
