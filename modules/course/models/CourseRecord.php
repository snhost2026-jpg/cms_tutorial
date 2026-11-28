<?php

namespace app\modules\course\models;

use Yii;
use yii\db\ActiveRecord;

abstract class CourseRecord extends ActiveRecord
{
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = time();
        $userId = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;

        if ($insert) {
            $this->setAttribute('created_at', $now);
            $this->setAttribute('created_by', $userId);
        }

        $this->setAttribute('updated_at', $now);
        $this->setAttribute('updated_by', $userId);

        return true;
    }

    public function getCreatedByUser()
    {
        return $this->hasOne(\app\models\User::class, ['id' => 'created_by']);
    }

    public function getUpdatedByUser()
    {
        return $this->hasOne(\app\models\User::class, ['id' => 'updated_by']);
    }
}
