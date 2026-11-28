<?php

namespace app\modules\exam\models;

use yii\db\ActiveRecord;

/**
 * Pivot mapping questions from the Question Bank into an Exam.
 *
 * @property int $id
 * @property int $exam_id
 * @property int $question_id
 * @property int $sort_order
 */
class ExamQuestionMap extends ActiveRecord
{
    public static function tableName()
    {
        return 'exam_question_map';
    }

    public function rules()
    {
        return [
            [['exam_id', 'question_id'], 'required'],
            [['exam_id', 'question_id', 'sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
            [['exam_id', 'question_id'], 'unique', 'targetAttribute' => ['exam_id', 'question_id']],
        ];
    }

    public function getExam()
    {
        return $this->hasOne(Exam::class, ['id' => 'exam_id']);
    }

    public function getQuestion()
    {
        return $this->hasOne(ExamQuestion::class, ['id' => 'question_id']);
    }
}
