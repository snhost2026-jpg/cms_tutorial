<?php

namespace app\modules\exam\models;

use app\modules\course\models\Course;
use app\modules\course\models\Subject;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Reusable Question Bank item (MCQ).
 *
 * @property int $id
 * @property int|null $course_id
 * @property int|null $subject_id
 * @property string $question_text
 * @property string $option_a
 * @property string $option_b
 * @property string|null $option_c
 * @property string|null $option_d
 * @property string $correct_option
 * @property float $marks
 * @property string $difficulty
 * @property int $is_active
 * @property int|null $created_by
 * @property string|null $created_at
 * @property int|null $updated_by
 * @property string|null $updated_at
 */
class ExamQuestion extends ActiveRecord
{
    const DIFFICULTY_EASY = 'easy';
    const DIFFICULTY_MEDIUM = 'medium';
    const DIFFICULTY_HARD = 'hard';

    public static function tableName()
    {
        return 'exam_questions';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['question_text', 'option_a', 'option_b', 'correct_option'], 'required'],
            [['question_text'], 'string'],
            [['option_a', 'option_b', 'option_c', 'option_d'], 'trim'],
            [['option_a', 'option_b', 'option_c', 'option_d'], 'string', 'max' => 500],
            [['option_c', 'option_d'], 'default', 'value' => null],
            [['course_id', 'subject_id'], 'integer'],
            [['course_id', 'subject_id'], 'default', 'value' => null],
            [['marks'], 'number', 'min' => 0.25, 'max' => 1000],
            [['marks'], 'default', 'value' => 1],
            [['correct_option'], 'in', 'range' => ['A', 'B', 'C', 'D']],
            [['correct_option'], 'validateCorrectOptionNotEmpty'],
            [['difficulty'], 'in', 'range' => [self::DIFFICULTY_EASY, self::DIFFICULTY_MEDIUM, self::DIFFICULTY_HARD]],
            [['difficulty'], 'default', 'value' => self::DIFFICULTY_MEDIUM],
            [['is_active'], 'boolean'],
            [['is_active'], 'default', 'value' => 1],
        ];
    }

    public function validateCorrectOptionNotEmpty($attribute)
    {
        $map = [
            'A' => $this->option_a,
            'B' => $this->option_b,
            'C' => $this->option_c,
            'D' => $this->option_d,
        ];
        if (!isset($map[$this->$attribute]) || trim((string) $map[$this->$attribute]) === '') {
            $this->addError($attribute, 'The selected correct option must not be empty.');
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_id' => 'Course',
            'subject_id' => 'Subject',
            'question_text' => 'Question',
            'option_a' => 'Option A',
            'option_b' => 'Option B',
            'option_c' => 'Option C (Optional)',
            'option_d' => 'Option D (Optional)',
            'correct_option' => 'Correct Answer',
            'marks' => 'Marks',
            'difficulty' => 'Difficulty',
            'is_active' => 'Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function difficultyOptions(): array
    {
        return [
            self::DIFFICULTY_EASY => 'Easy',
            self::DIFFICULTY_MEDIUM => 'Medium',
            self::DIFFICULTY_HARD => 'Hard',
        ];
    }

    public static function optionKeys(): array
    {
        return [
            'A' => 'Option A',
            'B' => 'Option B',
            'C' => 'Option C',
            'D' => 'Option D',
        ];
    }

    /** Returns non-empty options keyed by A, B, C, D */
    public function getAvailableOptions(): array
    {
        $options = [];
        foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $key => $attr) {
            if ($this->$attr !== null && trim((string) $this->$attr) !== '') {
                $options[$key] = $this->$attr;
            }
        }
        return $options;
    }

    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }

    public function getSubject()
    {
        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
    }
}
