<?php

namespace app\modules\academic\models;

class Batch extends AcademicRecord
{
    public static function tableName()
    {
        return '{{%batches}}';
    }

    public function rules()
    {
        return [
            [['course_id', 'capacity'], 'integer'],
            [['course_id'], 'exist', 'targetClass' => Course::class, 'targetAttribute' => ['course_id' => 'id']],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 150],
        ];
    }

    public function attributeLabels()
    {
        return [
            'course_id' => 'Course',
            'name' => 'Batch Name',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'capacity' => 'Capacity',
        ];
    }

    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }

    public function getSections()
    {
        return $this->hasMany(Section::class, ['batch_id' => 'id']);
    }
}
