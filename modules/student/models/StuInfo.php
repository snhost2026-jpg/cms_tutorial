<?php

namespace app\modules\student\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Table "stu_info": personal data, parents, guardian, address, previous school
 * and emergency contact of a student.
 *
 * No attribute is mandatory. Empty strings are stored as NULL.
 *
 * @property int $stu_info_id
 * @property string $stu_unique_id
 * @property int $stu_info_stu_master_id
 * @property string $application_no
 * @property string $stu_title
 * @property string $stu_first_name
 * @property string $stu_middle_name
 * @property string $stu_last_name
 * @property string $stu_gender
 * @property string $stu_dob
 * @property string $stu_birthplace
 * @property string $stu_religion
 * @property string $stu_aadhaar
 * @property string $stu_mobile_no
 * @property string $stu_whatsapp_no
 * @property string $stu_email_id
 * @property string $stu_admission_date
 * @property string $father_name
 * @property string $father_mobile
 * @property string $father_whatsapp
 * @property string $father_email
 * @property string $father_occupation
 * @property string $mother_name
 * @property string $mother_mobile
 * @property string $mother_whatsapp
 * @property string $mother_email
 * @property string $mother_occupation
 * @property string $guardian_name
 * @property string $guardian_relation
 * @property string $guardian_mobile
 * @property string $address_line
 * @property string $city
 * @property string $state
 * @property string $pincode
 * @property string $prev_school_name
 * @property string $prev_class
 * @property string $emergency_contact_name
 * @property string $emergency_contact_phone
 * @property string $Profile_image
 *
 * @property StuMaster $master
 * @property string $fullName
 */
class StuInfo extends ActiveRecord
{
    public $profileImageFile;

    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';
    const GENDER_OTHER = 'OTHER';

    public static function tableName()
    {
        return 'stu_info';
    }

    /** All data columns (everything except the two key columns). */
    public static function dataAttributes()
    {
        return [
            'stu_unique_id', 'application_no', 'stu_title', 'stu_first_name',
            'stu_middle_name', 'stu_last_name', 'stu_gender', 'stu_dob',
            'stu_birthplace', 'stu_religion', 'stu_aadhaar',
            'stu_mobile_no', 'stu_whatsapp_no', 'stu_email_id',
            'stu_admission_date',
            'father_name', 'father_mobile', 'father_whatsapp', 'father_email', 'father_occupation',
            'mother_name', 'mother_mobile', 'mother_whatsapp', 'mother_email', 'mother_occupation',
            'guardian_name', 'guardian_relation', 'guardian_mobile',
            'address_line', 'city', 'state', 'pincode',
            'prev_school_name', 'prev_class',
            'emergency_contact_name', 'emergency_contact_phone',
            'Profile_image',
        ];
    }

    public static function phoneAttributes()
    {
        return [
            'stu_mobile_no', 'stu_whatsapp_no', 'father_mobile', 'father_whatsapp',
            'mother_mobile', 'mother_whatsapp', 'guardian_mobile', 'emergency_contact_phone',
        ];
    }

    public function rules()
    {
        $all = self::dataAttributes();

        return [
            // clean input first: trim, then turn empty values into NULL
            [$all, 'trim'],
            [$all, 'default', 'value' => null],

            [['stu_info_stu_master_id'], 'integer'],
            [['stu_info_stu_master_id'], 'default', 'value' => null],
            [['Profile_image'], 'string', 'max' => 255],
            [['profileImageFile'], 'image', 'extensions' => 'png,jpg,jpeg,webp', 'maxSize' => 5 * 1024 * 1024],

            [['stu_title'], 'string', 'max' => 15],
            [['stu_gender'], 'string', 'max' => 20],
            [['stu_gender'], 'in', 'range' => array_keys(self::genderOptions())],
            [['stu_unique_id', 'guardian_relation', 'prev_class'], 'string', 'max' => 30],
            [['application_no', 'stu_religion'], 'string', 'max' => 50],
            [['city', 'state'], 'string', 'max' => 80],
            [['stu_birthplace', 'father_name', 'father_occupation', 'mother_name',
              'mother_occupation', 'guardian_name', 'emergency_contact_name'], 'string', 'max' => 100],
            [['stu_first_name', 'stu_middle_name', 'stu_last_name', 'stu_email_id',
              'father_email', 'mother_email', 'prev_school_name'], 'string', 'max' => 150],
            [['address_line'], 'string', 'max' => 255],

            [['stu_email_id', 'father_email', 'mother_email'], 'email'],

            [self::phoneAttributes(), 'string', 'max' => 15],
            [self::phoneAttributes(), 'match', 'pattern' => '/^\+?[0-9][0-9 \-]{5,13}$/',
                'message' => '{attribute} must be a valid phone number (digits, spaces or dashes).'],

            [['pincode'], 'match', 'pattern' => '/^[0-9]{4,10}$/',
                'message' => 'Pincode must contain 4 to 10 digits.'],
            [['stu_aadhaar'], 'match', 'pattern' => '/^[0-9]{12}$/',
                'message' => 'Aadhaar number must be exactly 12 digits.'],

            [['stu_dob', 'stu_admission_date'], 'date', 'format' => 'php:Y-m-d'],
            [['stu_dob'], 'validateDob'],

            // uniqueness is only checked when a value is given (NULLs never clash)
            [['stu_unique_id', 'stu_aadhaar', 'stu_email_id'], 'unique'],
        ];
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
            'stu_info_id' => 'ID',
            'stu_unique_id' => 'Registration No',
            'stu_info_stu_master_id' => 'Student Record',
            'application_no' => 'Application No',
            'stu_title' => 'Title',
            'stu_first_name' => 'First Name',
            'stu_middle_name' => 'Middle Name',
            'stu_last_name' => 'Last Name',
            'stu_gender' => 'Gender',
            'stu_dob' => 'Date of Birth',
            'stu_birthplace' => 'Birthplace',
            'stu_religion' => 'Religion',
            'stu_aadhaar' => 'Aadhaar No',
            'stu_mobile_no' => 'Mobile No',
            'stu_whatsapp_no' => 'WhatsApp No',
            'stu_email_id' => 'Email',
            'stu_admission_date' => 'Admission Date',
            'father_name' => "Father's Name",
            'father_mobile' => "Father's Mobile",
            'father_whatsapp' => "Father's WhatsApp",
            'father_email' => "Father's Email",
            'father_occupation' => "Father's Occupation",
            'mother_name' => "Mother's Name",
            'mother_mobile' => "Mother's Mobile",
            'mother_whatsapp' => "Mother's WhatsApp",
            'mother_email' => "Mother's Email",
            'mother_occupation' => "Mother's Occupation",
            'guardian_name' => 'Guardian Name',
            'guardian_relation' => 'Relation to Student',
            'guardian_mobile' => 'Guardian Mobile',
            'address_line' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'pincode' => 'Pincode',
            'prev_school_name' => 'Previous School',
            'prev_class' => 'Previous Class',
            'emergency_contact_name' => 'Emergency Contact Name',
            'emergency_contact_phone' => 'Emergency Contact Phone',
            'Profile_image' => 'Profile Image',
            'profileImageFile' => 'Profile Image',
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
        return ['Master' => 'Master', 'Miss' => 'Miss', 'Mr.' => 'Mr.', 'Ms.' => 'Ms.'];
    }

    /** Student's display name; never empty. */
    public function getFullName()
    {
        $parts = array_filter([$this->stu_first_name, $this->stu_middle_name, $this->stu_last_name], 'strlen');
        return $parts ? implode(' ', $parts) : '(no name)';
    }

    public function getMaster()
    {
        return $this->hasOne(StuMaster::class, ['stu_master_id' => 'stu_info_stu_master_id']);
    }
}
