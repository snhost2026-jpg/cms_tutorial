<?php

namespace app\modules\exam\models;

use yii\db\ActiveRecord;

/**
 * Per-question saved response for an exam attempt.
 *
 * @property int $id
 * @property int $attempt_id
 * @property int $question_id
 * @property string|null $selected_option
 * @property int $is_marked_review
 * @property int|null $is_correct
 * @property float $marks_awarded
 * @property string|null $updated_at
 */
class ExamAttemptAnswer extends ActiveRecord
{
    public static function tableName()
    {
        return 'exam_attempt_answers';
    }

    public function rules()
    {
        return [
            [['attempt_id', 'question_id'], 'required'],
            [['attempt_id', 'question_id'], 'integer'],
            [['selected_option'], 'in', 'range' => ['A', 'B', 'C', 'D']],
            [['selected_option'], 'default', 'value' => null],
            [['is_marked_review', 'is_correct'], 'boolean'],
            [['is_marked_review'], 'default', 'value' => 0],
            [['marks_awarded'], 'number'],
            [['marks_awarded'], 'default', 'value' => 0],
            [['updated_at'], 'safe'],
            [['attempt_id', 'question_id'], 'unique', 'targetAttribute' => ['attempt_id', 'question_id']],
        ];
    }

    public function getAttempt()
    {
        return $this->hasOne(ExamAttempt::class, ['id' => 'attempt_id']);
    }

    public function getQuestion()
    {
        return $this->hasOne(ExamQuestion::class, ['id' => 'question_id']);
    }
}
