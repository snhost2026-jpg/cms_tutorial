<?php

namespace app\modules\employee\models;

use yii\db\ActiveRecord;

class EmployeeInfo extends ActiveRecord
{
    public const TYPE_TEACHING = 'teaching';
    public const TYPE_NON_TEACHING = 'non_teaching';

    public const GENDER_MALE = 'MALE';
    public const GENDER_FEMALE = 'FEMALE';
    public const GENDER_OTHER = 'OTHER';

    public static function tableName()
    {
        return '{{%emp_info}}';
    }

    public static function dataAttributes()
    {
        return [
            'employee_code',
            'emp_application_no',
            'emp_title',
            'emp_first_name',
            'emp_middle_name',
            'emp_last_name',
            'emp_gender',
            'emp_dob',
            'emp_birthplace',
            'emp_religion',
            'emp_aadhaar',
            'emp_mobile_no',
            'emp_whatsapp_no',
            'emp_email_id',
            'emp_joining_date',
            'emp_father_name',
            'emp_father_mobile',
            'emp_father_whatsapp',
            'emp_father_email',
            'emp_father_occupation',
            'emp_mother_name',
            'emp_mother_mobile',
            'emp_mother_whatsapp',
            'emp_mother_email',
            'emp_mother_occupation',
            'emp_guardian_name',
            'emp_guardian_relation',
            'emp_guardian_mobile',
            'address_line',
            'city',
            'state',
            'pincode',
            'previous_employer_name',
            'previous_designation',
            'emergency_contact_name',
            'emergency_contact_phone',
            'employee_type',
            'designation',
            'department',
        ];
    }

    public static function phoneAttributes()
    {
        return [
            'emp_mobile_no',
            'emp_whatsapp_no',
            'emp_father_mobile',
            'emp_father_whatsapp',
            'emp_mother_mobile',
            'emp_mother_whatsapp',
            'emp_guardian_mobile',
            'emergency_contact_phone',
        ];
    }

    public function rules()
    {
        $all = self::dataAttributes();

        return [
            [$all, 'trim'],
            [$all, 'default', 'value' => null],
            [['emp_first_name', 'employee_type'], 'required'],
            [['employee_code'], 'required', 'when' => static function ($model) {
                return $model->isNewRecord;
            }],

            [['employee_code'], 'string', 'max' => 32],
            [['emp_application_no'], 'string', 'max' => 50],
            [['emp_title'], 'string', 'max' => 15],
            [['emp_gender'], 'string', 'max' => 20],
            [['emp_gender'], 'in', 'range' => array_keys(self::genderOptions())],
            [['emp_first_name', 'emp_middle_name', 'emp_last_name', 'emp_email_id',
              'emp_father_email', 'emp_mother_email'], 'string', 'max' => 150],
            [['emp_birthplace', 'emp_father_name', 'emp_father_occupation',
              'emp_mother_name', 'emp_mother_occupation', 'emp_guardian_name',
              'emergency_contact_name'], 'string', 'max' => 100],
            [['emp_religion'], 'string', 'max' => 50],
            [['emp_aadhaar'], 'string', 'max' => 12],
            [['emp_aadhaar'], 'match', 'pattern' => '/^[0-9]{12}$/',
                'message' => 'Aadhaar number must be exactly 12 digits.'],
            [['emp_email_id', 'emp_father_email', 'emp_mother_email'], 'email'],
            [['emp_mobile_no'], 'string', 'max' => 30],
            [array_values(array_diff(self::phoneAttributes(), ['emp_mobile_no'])), 'string', 'max' => 15],
            [self::phoneAttributes(), 'match', 'pattern' => '/^\+?[0-9][0-9 \-]{5,28}$/',
                'message' => '{attribute} must be a valid phone number (digits, spaces or dashes).'],
            [['emp_guardian_relation'], 'string', 'max' => 30],
            [['address_line'], 'string', 'max' => 255],
            [['city', 'state'], 'string', 'max' => 80],
            [['pincode'], 'match', 'pattern' => '/^[0-9]{4,10}$/',
                'message' => 'Pincode must contain 4 to 10 digits.'],
            [['previous_employer_name'], 'string', 'max' => 150],
            [['previous_designation', 'designation', 'department'], 'string', 'max' => 100],
            [['employee_type'], 'in', 'range' => array_keys(self::getTypeOptions())],
            [['emp_dob', 'emp_joining_date'], 'date', 'format' => 'php:Y-m-d'],
            [['emp_dob'], 'validateDob'],
            [['employee_code'], 'unique'],
            [['employee_code'], 'validateLoginUsernameUnique', 'when' => static function ($model) {
                return $model->isNewRecord;
            }],
        ];
    }

    public function validateLoginUsernameUnique($attribute)
    {
        if (
            $this->$attribute !== null
            && \app\models\User::find()->where(['username' => $this->$attribute])->exists()
        ) {
            $this->addError($attribute, 'This employee code is already used by a login account.');
        }
    }

    public function validateDob($attribute)
    {
        if (!empty($this->$attribute) && $this->$attribute > date('Y-m-d')) {
            $this->addError($attribute, 'Date of birth cannot be in the future.');
        }
    }

    public function attributeLabels()
    {
        return [
            'emp_info_id' => 'Employee Info ID',
            'emp_info_emp_master_id' => 'Employee Record',
            'employee_code' => 'Employee Code',
            'emp_application_no' => 'Application No',
            'emp_title' => 'Title',
            'emp_first_name' => 'First Name',
            'emp_middle_name' => 'Middle Name',
            'emp_last_name' => 'Last Name',
            'emp_gender' => 'Gender',
            'emp_dob' => 'Date of Birth',
            'emp_birthplace' => 'Birthplace',
            'emp_religion' => 'Religion',
            'emp_aadhaar' => 'Aadhaar No',
            'emp_mobile_no' => 'Mobile No',
            'emp_whatsapp_no' => 'WhatsApp No',
            'emp_email_id' => 'Email',
            'emp_joining_date' => 'Joining Date',
            'emp_father_name' => "Father's Name",
            'emp_father_mobile' => "Father's Mobile",
            'emp_father_whatsapp' => "Father's WhatsApp",
            'emp_father_email' => "Father's Email",
            'emp_father_occupation' => "Father's Occupation",
            'emp_mother_name' => "Mother's Name",
            'emp_mother_mobile' => "Mother's Mobile",
            'emp_mother_whatsapp' => "Mother's WhatsApp",
            'emp_mother_email' => "Mother's Email",
            'emp_mother_occupation' => "Mother's Occupation",
            'emp_guardian_name' => 'Guardian Name',
            'emp_guardian_relation' => 'Relation to Employee',
            'emp_guardian_mobile' => 'Guardian Mobile',
            'address_line' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'pincode' => 'Pincode',
            'previous_employer_name' => 'Previous Employer',
            'previous_designation' => 'Previous Designation',
            'emergency_contact_name' => 'Emergency Contact Name',
            'emergency_contact_phone' => 'Emergency Contact Phone',
            'employee_type' => 'Employee Type',
            'designation' => 'Designation',
            'department' => 'Department',
            'fullName' => 'Name',
        ];
    }

    public static function genderOptions()
    {
        return [
            self::GENDER_MALE => 'Male',
            self::GENDER_FEMALE => 'Female',
            self::GENDER_OTHER => 'Other',
        ];
    }

    public static function titleOptions()
    {
        return [
            'Mr.' => 'Mr.',
            'Mrs.' => 'Mrs.',
            'Ms.' => 'Ms.',
            'Dr.' => 'Dr.',
        ];
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_TEACHING => 'Teaching',
            self::TYPE_NON_TEACHING => 'Non-teaching',
        ];
    }

    public function getFullName()
    {
        $parts = array_filter([
            $this->emp_first_name,
            $this->emp_middle_name,
            $this->emp_last_name,
        ], static function ($part) {
            return $part !== null && $part !== '';
        });
        return $parts ? implode(' ', $parts) : '(no name)';
    }

    public function getMaster()
    {
        return $this->hasOne(Employee::class, ['emp_master_id' => 'emp_info_emp_master_id']);
    }
}
