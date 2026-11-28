<?php

namespace app\modules\timetable\models;

use Yii;
use app\modules\course\models\Batch;
use app\modules\course\models\Course;
use app\modules\course\models\Section;
use app\modules\course\models\Subject;
use app\modules\employee\models\Employee;
use yii\db\ActiveRecord;

/**
 * ActiveRecord model for `coaching_timetable`.
 *
 * @property int $id
 * @property int $academic_session_id
 * @property int $course_id
 * @property int $batch_id
 * @property int|null $section_id
 * @property int $subject_id
 * @property int $employee_id
 * @property int $day_of_week
 * @property string $start_time
 * @property string $end_time
 * @property int|null $room_id
 * @property string $class_type
 * @property string $effective_from
 * @property string|null $effective_to
 * @property int $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read Course|null $course
 * @property-read Batch|null $batch
 * @property-read Section|null $section
 * @property-read Subject|null $subject
 * @property-read Employee|null $employee
 */
class CoachingTimetable extends ActiveRecord
{
    public const DAY_MONDAY = 1;
    public const DAY_TUESDAY = 2;
    public const DAY_WEDNESDAY = 3;
    public const DAY_THURSDAY = 4;
    public const DAY_FRIDAY = 5;
    public const DAY_SATURDAY = 6;
    public const DAY_SUNDAY = 7;

    public const TYPE_THEORY = 'Theory';
    public const TYPE_PRACTICAL = 'Practical';
    public const TYPE_REVISION = 'Revision';
    public const TYPE_TEST = 'Test';
    public const TYPE_DOUBT_CLEARING = 'Doubt Clearing';

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * Optional multi-day selection array (1..7) used when creating recurring weekly classes.
     * @var array<int|string>
     */
    public $days_of_week = [];

    public static function tableName()
    {
        return 'coaching_timetable';
    }

    public function init(): void
    {
        parent::init();
        if ($this->isNewRecord) {
            if ($this->status === null) {
                $this->status = self::STATUS_ACTIVE;
            }
            if ($this->class_type === null) {
                $this->class_type = self::TYPE_THEORY;
            }
        }
    }

    public static function dayOptions(): array
    {
        return [
            self::DAY_MONDAY => 'Monday',
            self::DAY_TUESDAY => 'Tuesday',
            self::DAY_WEDNESDAY => 'Wednesday',
            self::DAY_THURSDAY => 'Thursday',
            self::DAY_FRIDAY => 'Friday',
            self::DAY_SATURDAY => 'Saturday',
            self::DAY_SUNDAY => 'Sunday',
        ];
    }

    public static function dayShortOptions(): array
    {
        return [
            self::DAY_MONDAY => 'Mon',
            self::DAY_TUESDAY => 'Tue',
            self::DAY_WEDNESDAY => 'Wed',
            self::DAY_THURSDAY => 'Thu',
            self::DAY_FRIDAY => 'Fri',
            self::DAY_SATURDAY => 'Sat',
            self::DAY_SUNDAY => 'Sun',
        ];
    }

    public static function classTypeOptions(): array
    {
        return [
            self::TYPE_THEORY => self::TYPE_THEORY,
            self::TYPE_PRACTICAL => self::TYPE_PRACTICAL,
            self::TYPE_REVISION => self::TYPE_REVISION,
            self::TYPE_TEST => self::TYPE_TEST,
            self::TYPE_DOUBT_CLEARING => self::TYPE_DOUBT_CLEARING,
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    /**
     * Returns UI styling metadata (Bootstrap color accent & FontAwesome icon) for a class_type.
     */
    public static function classTypeMeta(?string $type): array
    {
        $map = [
            self::TYPE_THEORY => [
                'badge' => 'bg-primary-subtle text-primary border-primary-subtle',
                'accent' => '#2563eb',
                'softBg' => '#eff6ff',
                'icon' => 'fa-book-open',
            ],
            self::TYPE_PRACTICAL => [
                'badge' => 'bg-success-subtle text-success border-success-subtle',
                'accent' => '#16a34a',
                'softBg' => '#f0fdf4',
                'icon' => 'fa-flask',
            ],
            self::TYPE_REVISION => [
                'badge' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                'accent' => '#0891b2',
                'softBg' => '#ecfeff',
                'icon' => 'fa-rotate',
            ],
            self::TYPE_TEST => [
                'badge' => 'bg-danger-subtle text-danger border-danger-subtle',
                'accent' => '#dc2626',
                'softBg' => '#fef2f2',
                'icon' => 'fa-file-pen',
            ],
            self::TYPE_DOUBT_CLEARING => [
                'badge' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                'accent' => '#d97706',
                'softBg' => '#fffbeb',
                'icon' => 'fa-circle-question',
            ],
        ];

        return $map[$type] ?? $map[self::TYPE_THEORY];
    }

    public function rules()
    {
        return [
            [['section_id', 'room_id', 'effective_to', 'start_time', 'end_time', 'effective_from'], 'trim'],
            [['section_id', 'room_id', 'effective_to'], 'default', 'value' => null],
            [['class_type'], 'default', 'value' => self::TYPE_THEORY],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],

            [
                [
                    'academic_session_id',
                    'course_id',
                    'batch_id',
                    'subject_id',
                    'employee_id',
                    'day_of_week',
                    'start_time',
                    'end_time',
                    'effective_from',
                ],
                'required',
            ],

            [
                ['academic_session_id', 'course_id', 'batch_id', 'section_id', 'subject_id', 'employee_id', 'room_id'],
                'integer',
                'min' => 1,
            ],
            [['day_of_week'], 'integer', 'min' => self::DAY_MONDAY, 'max' => self::DAY_SUNDAY],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['class_type'], 'in', 'range' => array_keys(self::classTypeOptions())],

            [['start_time', 'end_time'], 'normalizeTimeAttribute'],
            [['start_time', 'end_time'], 'validateTimeFormat'],
            [['end_time'], 'validateTimeOrder'],

            [['effective_from', 'effective_to'], 'date', 'format' => 'php:Y-m-d'],
            [['effective_to'], 'validateEffectiveDates'],

            [['course_id', 'batch_id', 'section_id', 'subject_id', 'employee_id'], 'validatePlacement'],
            [['start_time', 'employee_id', 'room_id'], 'validateScheduleConflicts'],
            [['days_of_week'], 'safe'],
        ];
    }

    public function normalizeTimeAttribute($attribute): void
    {
        $value = $this->$attribute;
        if (!is_string($value) || $value === '') {
            return;
        }
        $value = trim($value);
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $m)) {
            $this->$attribute = sprintf('%02d:%02d:00', (int) $m[1], (int) $m[2]);
        } elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $value, $m)) {
            $this->$attribute = sprintf('%02d:%02d:%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }
    }

    public function validateTimeFormat($attribute): void
    {
        $value = (string) $this->$attribute;
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $value)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' must be a valid 24-hour time (HH:MM).');
        }
    }

    public function validateTimeOrder($attribute): void
    {
        if ($this->hasErrors('start_time') || $this->hasErrors('end_time')) {
            return;
        }
        if ($this->start_time >= $this->end_time) {
            $this->addError($attribute, 'End time must be later than start time.');
        }
    }

    public function validateEffectiveDates($attribute): void
    {
        if ($this->hasErrors('effective_from') || $this->hasErrors('effective_to')) {
            return;
        }
        if ($this->effective_to !== null && $this->effective_to !== '' && $this->effective_to < $this->effective_from) {
            $this->addError($attribute, 'Effective To date must be on or after Effective From date.');
        }
    }

    public function validatePlacement($attribute): void
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        try {
            if ($attribute === 'course_id' && class_exists(Course::class) && $this->tableExists(Course::tableName())) {
                if (!Course::find()->where(['id' => (int) $this->course_id])->exists()) {
                    $this->addError('course_id', 'Selected course does not exist.');
                }
            }

            if ($attribute === 'batch_id' && class_exists(Batch::class) && $this->tableExists(Batch::tableName())) {
                $batch = Batch::findOne((int) $this->batch_id);
                if ($batch === null) {
                    $this->addError('batch_id', 'Selected batch does not exist.');
                } elseif ($this->course_id && (int) $batch->course_id !== (int) $this->course_id) {
                    $this->addError('batch_id', 'Selected batch does not belong to the chosen course.');
                }
            }

            if (
                $attribute === 'section_id'
                && $this->section_id !== null
                && class_exists(Section::class)
                && $this->tableExists(Section::tableName())
            ) {
                $section = Section::findOne((int) $this->section_id);
                if ($section === null) {
                    $this->addError('section_id', 'Selected section does not exist.');
                } elseif (
                    ($this->course_id && (int) $section->course_id !== (int) $this->course_id)
                    || ($this->batch_id && (int) $section->batch_id !== (int) $this->batch_id)
                ) {
                    $this->addError('section_id', 'Selected section does not belong to the chosen course and batch.');
                }
            }

            if ($attribute === 'subject_id' && class_exists(Subject::class) && $this->tableExists(Subject::tableName())) {
                $subject = Subject::findOne((int) $this->subject_id);
                if ($subject === null) {
                    $this->addError('subject_id', 'Selected subject does not exist.');
                } elseif ($this->course_id && $subject->course_id !== null && (int) $subject->course_id !== (int) $this->course_id) {
                    $this->addError('subject_id', 'Selected subject does not belong to the chosen course.');
                }
            }

            if ($attribute === 'employee_id' && class_exists(Employee::class) && $this->tableExists(Employee::tableName())) {
                if (!Employee::find()->where(['emp_master_id' => (int) $this->employee_id])->exists()) {
                    $this->addError('employee_id', 'Selected teacher/employee does not exist.');
                }
            }
        } catch (\Throwable $e) {
            // Skip cross-table validation if related schema is unavailable
        }
    }

    /**
     * Prevents double-booking teachers, batches/sections, and rooms on active overlapping slots.
     */
    public function validateScheduleConflicts($attribute): void
    {
        if ((int) $this->status !== self::STATUS_ACTIVE) {
            return;
        }
        if (
            $this->hasErrors('day_of_week')
            || $this->hasErrors('start_time')
            || $this->hasErrors('end_time')
            || $this->hasErrors('effective_from')
            || $this->hasErrors('effective_to')
        ) {
            return;
        }
        if (!$this->tableExists(self::tableName())) {
            return;
        }

        $baseQuery = self::find()
            ->where([
                'status' => self::STATUS_ACTIVE,
                'day_of_week' => (int) $this->day_of_week,
                'academic_session_id' => (int) $this->academic_session_id,
            ])
            ->andWhere(['<', 'start_time', $this->end_time])
            ->andWhere(['>', 'end_time', $this->start_time])
            ->andWhere([
                'or',
                ['effective_to' => null],
                ['>=', 'effective_to', $this->effective_from],
            ]);

        if ($this->effective_to !== null && $this->effective_to !== '') {
            $baseQuery->andWhere(['<=', 'effective_from', $this->effective_to]);
        }

        if (!$this->isNewRecord && $this->id) {
            $baseQuery->andWhere(['<>', 'id', (int) $this->id]);
        }

        $dayLabel = $this->getDayLabel();

        if ($attribute === 'employee_id' && !$this->hasErrors('employee_id')) {
            $teacherClash = (clone $baseQuery)
                ->andWhere(['employee_id' => (int) $this->employee_id])
                ->one();
            if ($teacherClash !== null) {
                $this->addError(
                    'employee_id',
                    sprintf(
                        'Teacher conflict: already scheduled on %s (%s).',
                        $dayLabel,
                        $teacherClash->getFormattedTimeRange()
                    )
                );
            }
        }

        if ($attribute === 'start_time' && !$this->hasErrors('batch_id')) {
            $batchQuery = (clone $baseQuery)->andWhere(['batch_id' => (int) $this->batch_id]);
            if ($this->section_id !== null && $this->section_id !== '') {
                $batchQuery->andWhere([
                    'or',
                    ['section_id' => null],
                    ['section_id' => (int) $this->section_id],
                ]);
            }
            $batchClash = $batchQuery->one();
            if ($batchClash !== null) {
                $this->addError(
                    'start_time',
                    sprintf(
                        'Batch/section conflict: another class is already scheduled on %s (%s).',
                        $dayLabel,
                        $batchClash->getFormattedTimeRange()
                    )
                );
            }
        }

        if ($attribute === 'room_id' && $this->room_id !== null && $this->room_id !== '' && !$this->hasErrors('room_id')) {
            $roomClash = (clone $baseQuery)
                ->andWhere(['room_id' => (int) $this->room_id])
                ->one();
            if ($roomClash !== null) {
                $this->addError(
                    'room_id',
                    sprintf(
                        'Room conflict: Room #%d is already occupied on %s (%s).',
                        (int) $this->room_id,
                        $dayLabel,
                        $roomClash->getFormattedTimeRange()
                    )
                );
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'academic_session_id' => 'Academic Session',
            'course_id' => 'Course',
            'batch_id' => 'Batch',
            'section_id' => 'Section',
            'subject_id' => 'Subject',
            'employee_id' => 'Teacher / Faculty',
            'day_of_week' => 'Day of Week',
            'days_of_week' => 'Days of Week',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'room_id' => 'Room',
            'class_type' => 'Class Type',
            'effective_from' => 'Effective From',
            'effective_to' => 'Effective To',
            'status' => 'Status',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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

    public function getSection()
    {
        return $this->hasOne(Section::class, ['id' => 'section_id']);
    }

    public function getSubject()
    {
        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['emp_master_id' => 'employee_id']);
    }

    public function getDayLabel(): string
    {
        return self::dayOptions()[(int) $this->day_of_week] ?? ('Day ' . $this->day_of_week);
    }

    public function getInputTime(string $attribute): string
    {
        $val = (string) $this->$attribute;
        return strlen($val) >= 5 ? substr($val, 0, 5) : $val;
    }

    public static function formatTimeLabel(?string $time): string
    {
        if (!$time) {
            return '-';
        }
        $ts = strtotime('1970-01-01 ' . $time);
        return $ts !== false ? date('h:i A', $ts) : $time;
    }

    public function getFormattedTimeRange(): string
    {
        return self::formatTimeLabel($this->start_time) . ' – ' . self::formatTimeLabel($this->end_time);
    }

    public function getDurationMinutes(): int
    {
        $start = strtotime('1970-01-01 ' . $this->start_time);
        $end = strtotime('1970-01-01 ' . $this->end_time);
        if ($start === false || $end === false || $end <= $start) {
            return 0;
        }
        return (int) round(($end - $start) / 60);
    }

    public function getTeacherDisplayName(): string
    {
        if ($this->employee && $this->employee->info) {
            return $this->employee->info->fullName;
        }
        return 'Employee #' . $this->employee_id;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $userId = (Yii::$app->has('user', true) && !Yii::$app->user->isGuest)
            ? (int) Yii::$app->user->id
            : null;
        $nowTimestamp = date('Y-m-d H:i:s');

        if ($insert) {
            if ($this->created_by === null) {
                $this->created_by = $userId;
            }
            if (empty($this->created_at)) {
                $this->created_at = $nowTimestamp;
            }
        }
        $this->updated_by = $userId ?? $this->updated_by;
        $this->updated_at = $nowTimestamp;

        return true;
    }

    private function tableExists(string $tableName): bool
    {
        try {
            return Yii::$app->db->schema->getTableSchema($tableName) !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
