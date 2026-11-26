<?php

namespace app\modules\academic\models;

class Subject extends AcademicRecord
{
    public static function tableName()
    {
        return '{{%subjects}}';
    }

    public function rules()
    {
        return [
            [['course_id'], 'integer'],
            [['course_id'], 'exist', 'targetClass' => Course::class, 'targetAttribute' => ['course_id' => 'id']],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 160],
            [['code'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'course_id' => 'Course',
            'name' => 'Subject Name',
            'code' => 'Code',
        ];
    }

    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }
}
