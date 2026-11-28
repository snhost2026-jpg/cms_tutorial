<?php

namespace app\modules\academic\models;

use Yii;
use app\modules\course\models\Batch;
use app\modules\course\models\Course;
use app\modules\course\models\Section;
use app\modules\course\models\Subject;
use app\modules\employee\models\Employee;
use yii\db\ActiveRecord;

class SubjectAssign extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%subject_assign}}';
    }

    public function rules()
    {
        return [
            [['subject_code', 'course_id', 'Batch_id', 'section_id', 'employ_master_id'], 'required'],
            [['course_id', 'Batch_id', 'section_id', 'employ_master_id'], 'integer'],
            [['subject_code'], 'string', 'max' => 50],
            [
                ['course_id'],
                'exist',
                'targetClass' => Course::class,
                'targetAttribute' => ['course_id' => 'id'],
            ],
            [
                ['Batch_id'],
                'exist',
                'targetClass' => Batch::class,
                'targetAttribute' => ['Batch_id' => 'id'],
            ],
            [
                ['section_id'],
                'exist',
                'targetClass' => Section::class,
                'targetAttribute' => ['section_id' => 'id'],
            ],
            [
                ['employ_master_id'],
                'exist',
                'targetClass' => Employee::class,
                'targetAttribute' => ['employ_master_id' => 'emp_master_id'],
            ],
            [
                ['subject_code', 'course_id', 'Batch_id', 'section_id'],
                'unique',
                'targetAttribute' => ['subject_code', 'course_id', 'Batch_id', 'section_id'],
                'message' => 'This subject is already assigned to the selected section.',
            ],
            [['subject_code', 'Batch_id', 'section_id'], 'validatePlacement'],
        ];
    }

    public function validatePlacement($attribute, $params)
    {
        if ($this->Batch_id && $this->course_id) {
            $batch = Batch::findOne($this->Batch_id);
            if ($batch === null || (int) $batch->course_id !== (int) $this->course_id) {
                $this->addError('Batch_id', 'The selected batch does not belong to the selected course.');
            }
        }

        if ($this->section_id) {
            $section = Section::findOne($this->section_id);
            if (
                $section === null
                || (int) $section->course_id !== (int) $this->course_id
                || (int) $section->batch_id !== (int) $this->Batch_id
            ) {
                $this->addError('section_id', 'The selected section does not belong to the selected course and batch.');
            }
        }

        if ($this->subject_code) {
            $subject = Subject::find()
                ->where(['course_id' => $this->course_id])
                ->andWhere([
                    'or',
                    ['code' => $this->subject_code],
                    ['id' => ctype_digit((string) $this->subject_code) ? (int) $this->subject_code : null],
                ])
                ->one();
            if ($subject === null) {
                $this->addError('subject_code', 'The selected subject does not belong to the selected course.');
            } elseif ($subject->code === null || $subject->code === '') {
                $this->subject_code = (string) $subject->id;
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'subject_code' => 'Subject Code',
            'course_id' => 'Course',
            'Batch_id' => 'Batch',
            'section_id' => 'Section',
            'employ_master_id' => 'Teacher',
        ];
    }

    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }

    public function getBatch()
    {
        return $this->hasOne(Batch::class, ['id' => 'Batch_id']);
    }

    public function getSection()
    {
        return $this->hasOne(Section::class, ['id' => 'section_id']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['emp_master_id' => 'employ_master_id']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = time();
        $userId = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;

        return true;
    }
}
