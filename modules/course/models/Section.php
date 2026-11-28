<?php

namespace app\modules\course\models;

class Section extends CourseRecord
{
    public static function tableName()
    {
        return '{{%sections}}';
    }

    public function rules()
    {
        return [
            [['course_id', 'batch_id'], 'integer'],
            [['course_id'], 'exist', 'targetClass' => Course::class, 'targetAttribute' => ['course_id' => 'id']],
            [
                ['batch_id'],
                'exist',
                'targetClass' => Batch::class,
                'targetAttribute' => ['batch_id' => 'id'],
                'filter' => function ($query) {
                    $query->andWhere(['course_id' => $this->getAttribute('course_id')]);
                },
            ],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 70],
        ];
    }

    public function attributeLabels()
    {
        return [
            'course_id' => 'Course',
            'batch_id' => 'Batch',
            'name' => 'Section Name',
        ];
    }

    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }

    public function getBatch()
    {
        return $this->hasOne(Batch::class, ['id' => 'batch_id']);
    }
}
