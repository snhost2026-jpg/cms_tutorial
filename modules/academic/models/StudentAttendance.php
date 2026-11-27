<?php

namespace app\modules\academic\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $student_id
 * @property int $section_id
 * @property int $subject_id
 * @property string $attendance_date
 * @property string $status
 * @property int|null $marked_by
 * @property int $created_at
 * @property int $updated_at
 */
class StudentAttendance extends ActiveRecord
{
    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';
    public const STATUS_EXCUSED = 'excused';

    public static function tableName()
    {
        return '{{%student_attendance}}';
    }

    public function rules()
    {
        return [
            [['student_id', 'section_id', 'subject_id'], 'required'],
            [['student_id', 'section_id', 'subject_id', 'marked_by', 'created_at', 'updated_at'], 'integer'],
            [['attendance_date', 'status'], 'required'],
            [['attendance_date'], 'date', 'format' => 'php:Y-m-d'],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [
                ['student_id', 'section_id', 'subject_id', 'attendance_date'],
                'unique',
                'targetAttribute' => ['student_id', 'section_id', 'subject_id', 'attendance_date'],
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'student_id' => 'Student',
            'section_id' => 'Section',
            'subject_id' => 'Subject',
            'attendance_date' => 'Attendance Date',
            'status' => 'Status',
            'marked_by' => 'Marked By',
        ];
    }

    public static function statusOptions()
    {
        return [
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_LATE => 'Late',
            self::STATUS_EXCUSED => 'Excused',
        ];
    }
}
