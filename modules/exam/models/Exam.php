<?php

namespace app\modules\exam\models;

use app\modules\course\models\Batch;
use app\modules\course\models\Course;
use app\modules\course\models\Section;
use app\modules\course\models\Subject;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Online Exam definition.
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int|null $course_id
 * @property int|null $batch_id
 * @property int|null $section_id
 * @property int|null $subject_id
 * @property int|null $duration_minutes
 * @property string|null $start_time
 * @property string|null $end_time
 * @property float|null $pass_percentage
 * @property int $shuffle_questions
 * @property int $show_result
 * @property string $status
 * @property int|null $created_by
 * @property string|null $created_at
 * @property int|null $updated_by
 * @property string|null $updated_at
 */
class Exam extends ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_CLOSED = 'closed';

    public static function tableName()
    {
        return 'exams';
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
            [['title'], 'required'],
            [['title'], 'trim'],
            [['title'], 'string', 'max' => 200],
            [['description'], 'string'],
            [['description'], 'default', 'value' => null],
            [['course_id', 'batch_id', 'section_id', 'subject_id', 'duration_minutes'], 'integer'],
            [['course_id', 'batch_id', 'section_id', 'subject_id', 'duration_minutes'], 'default', 'value' => null],
            [['duration_minutes'], 'integer', 'min' => 1, 'max' => 600],
            [['pass_percentage'], 'number', 'min' => 0, 'max' => 100],
            [['pass_percentage'], 'default', 'value' => null],
            [['start_time', 'end_time'], 'filter', 'filter' => [$this, 'normalizeDateTime']],
            [['start_time', 'end_time'], 'default', 'value' => null],
            [['end_time'], 'validateScheduleWindow'],
            [['shuffle_questions', 'show_result'], 'boolean'],
            [['shuffle_questions'], 'default', 'value' => 0],
            [['show_result'], 'default', 'value' => 1],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_CLOSED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
        ];
    }

    public function normalizeDateTime($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $ts = strtotime(str_replace('T', ' ', trim((string) $value)));
        return $ts !== false ? date('Y-m-d H:i:s', $ts) : $value;
    }

    public function validateScheduleWindow($attribute)
    {
        if ($this->start_time && $this->end_time) {
            if (strtotime($this->end_time) <= strtotime($this->start_time)) {
                $this->addError($attribute, 'End Date & Time must be after Start Date & Time.');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Exam Title',
            'description' => 'Instructions / Description',
            'course_id' => 'Course',
            'batch_id' => 'Batch',
            'section_id' => 'Section (Optional)',
            'subject_id' => 'Subject (Optional)',
            'duration_minutes' => 'Duration in Minutes (Optional)',
            'start_time' => 'Start Date & Time (Optional)',
            'end_time' => 'End Date & Time (Optional)',
            'pass_percentage' => 'Pass Percentage % (Optional)',
            'shuffle_questions' => 'Shuffle Questions',
            'show_result' => 'Show Result Immediately',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    public function getStatusBadgeClass(): string
    {
        switch ($this->status) {
            case self::STATUS_PUBLISHED:
                return 'bg-success-subtle text-success border border-success-subtle';
            case self::STATUS_CLOSED:
                return 'bg-secondary-subtle text-secondary border border-secondary-subtle';
            default:
                return 'bg-warning-subtle text-warning border border-warning-subtle';
        }
    }

    /** Formats DB datetime for HTML5 datetime-local input */
    public function formatForDateTimeInput(?string $value): string
    {
        if (!$value) {
            return '';
        }
        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d\TH:i', $ts) : '';
    }

    /**
     * Checks availability relative to current server time:
     * returns 'draft', 'closed', 'upcoming', 'expired', or 'open'.
     */
    public function getAvailabilityState(): string
    {
        if ($this->status === self::STATUS_DRAFT) {
            return 'draft';
        }
        if ($this->status === self::STATUS_CLOSED) {
            return 'closed';
        }

        $now = time();
        if ($this->start_time && $now < strtotime($this->start_time)) {
            return 'upcoming';
        }
        if ($this->end_time && $now > strtotime($this->end_time)) {
            return 'expired';
        }

        return 'open';
    }

    public function isOpenNow(): bool
    {
        return $this->getAvailabilityState() === 'open';
    }

    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }

    public function getBatch()
    {
        return $this->hasOne(Batch::class, ['id' => 'batch_id']);
    }

    public function getSection()
    {
        return $this->hasOne(Section::class, ['id' => 'section_id']);
    }

    public function getSubject()
    {
        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
    }

    public function getQuestionMaps()
    {
        return $this->hasMany(ExamQuestionMap::class, ['exam_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getQuestions()
    {
        return $this->hasMany(ExamQuestion::class, ['id' => 'question_id'])
            ->via('questionMaps');
    }

    public function getAttempts()
    {
        return $this->hasMany(ExamAttempt::class, ['exam_id' => 'id']);
    }

    public function getQuestionCount(): int
    {
        return (int) ExamQuestionMap::find()->where(['exam_id' => $this->id])->count();
    }

    public function getTotalMarks(): float
    {
        $sum = ExamQuestion::find()
            ->innerJoin('exam_question_map m', 'm.question_id = exam_questions.id')
            ->where(['m.exam_id' => $this->id])
            ->sum('exam_questions.marks');
        return (float) ($sum ?: 0);
    }
}
