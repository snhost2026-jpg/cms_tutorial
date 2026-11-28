<?php

namespace app\modules\employee\models;

use Yii;
use yii\db\ActiveRecord;

class Employee extends ActiveRecord
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    public static function tableName()
    {
        return '{{%emp_master}}';
    }

    public function rules()
    {
        return [
            [['emp_master_user_id'], 'integer'],
            [['emp_master_user_id'], 'default', 'value' => null],
            [['is_status'], 'in', 'range' => array_keys(self::getStatusOptions())],
            [['is_status'], 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    public function attributeLabels()
    {
        return [
            'emp_master_id' => 'Employee ID',
            'emp_master_emp_info_id' => 'Employee Info',
            'emp_master_user_id' => 'Login User',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'is_status' => 'Status',
        ];
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public function getInfo()
    {
        return $this->hasOne(EmployeeInfo::class, ['emp_info_id' => 'emp_master_emp_info_id']);
    }

    public function beforeSave($insert)
    {
        $now = time();
        $userId = Yii::$app->has('user', true) && !Yii::$app->user->isGuest
            ? Yii::$app->user->id
            : null;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;

        return parent::beforeSave($insert);
    }
}
