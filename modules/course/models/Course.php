<?php

namespace app\modules\course\models;

class Course extends CourseRecord
{
    public static function tableName()
    {
        return '{{%courses}}';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['fee_amount'], 'number'],
            [['active'], 'boolean'],
            [['name'], 'string', 'max' => 150],
            [['code'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Course Name',
            'code' => 'Code',
            'description' => 'Description',
            'fee_amount' => 'Fee Amount',
            'active' => 'Active',
        ];
    }

    public function getSubjects()
    {
        return $this->hasMany(Subject::class, ['course_id' => 'id']);
    }

    public function getBatches()
    {
        return $this->hasMany(Batch::class, ['course_id' => 'id']);
    }
}
