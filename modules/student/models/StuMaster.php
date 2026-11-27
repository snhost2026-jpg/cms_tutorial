<?php

namespace app\modules\student\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Table "stu_master": class placement, link to the login account, audit
 * columns and soft delete.
 *
 * is_status: 0 = inactive, 1 = active, 2 = deleted (hidden by find()).
 * No attribute is mandatory and no foreign key constraints exist.
 *
 * @property int $stu_master_id
 * @property int $stu_master_stu_info_id
 * @property int $stu_master_user_id
 * @property int $stu_master_course_id
 * @property int $stu_master_batch_id
 * @property int $stu_master_section_id
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property int $is_status
 *
 * @property StuInfo $info
 */
class StuMaster extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    public static function tableName()
    {
        return 'stu_master';
    }

    /** Default query: soft-deleted rows are hidden. */
    public static function find()
    {
        return parent::find()->andWhere(['<>', 'stu_master.is_status', self::STATUS_DELETED]);
    }

    /** Query that also returns soft-deleted rows (used by the trash view). */
    public static function findWithDeleted()
    {
        return Yii::createObject(ActiveQuery::class, [get_called_class()]);
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
            'blame' => [
                'class' => BlameableBehavior::class,
            ],
        ];
    }

    public function rules()
    {
        $ids = [
            'stu_master_user_id', 'stu_master_course_id', 'stu_master_batch_id',
            'stu_master_section_id',
        ];

        return [
            [$ids, 'integer'],
            [$ids, 'default', 'value' => null],
            // the form can only toggle active/inactive; deleting goes through softDelete()
            [['is_status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
            [['is_status'], 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    public function attributeLabels()
    {
        return [
            'stu_master_id' => 'ID',
            'stu_master_stu_info_id' => 'Student Info',
            'stu_master_user_id' => 'Login User',
            'stu_master_course_id' => 'Course / Class',
            'stu_master_batch_id' => 'Batch / Year',
            'stu_master_section_id' => 'Section',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'is_status' => 'Status',
        ];
    }

    public static function statusOptions()
    {
        return [self::STATUS_ACTIVE => 'Active', self::STATUS_INACTIVE => 'Inactive'];
    }

    public function getStatusLabel()
    {
        $all = self::statusOptions() + [self::STATUS_DELETED => 'Deleted'];
        return isset($all[$this->is_status]) ? $all[$this->is_status] : '-';
    }

    public function getInfo()
    {
        return $this->hasOne(StuInfo::class, ['stu_info_id' => 'stu_master_stu_info_id']);
    }

    public function softDelete()
    {
        $this->is_status = self::STATUS_DELETED;
        return $this->save(false, ['is_status', 'updated_at', 'updated_by']);
    }

    public function restore()
    {
        $this->is_status = self::STATUS_ACTIVE;
        return $this->save(false, ['is_status', 'updated_at', 'updated_by']);
    }
}
