<?php

use yii\db\Migration;

/**
 * Creates stu_info and stu_master (same structure as sql/schema.sql).
 * No foreign keys; every column except the primary keys is nullable.
 *
 * Run with:
 *   php yii migrate --migrationPath=@app/modules/student/migrations
 */
class m260101_000001_create_student_tables extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            : null;

        $this->createTable('stu_info', [
            'stu_info_id' => $this->primaryKey()->unsigned(),
            'stu_unique_id' => $this->string(30),
            'stu_info_stu_master_id' => $this->integer()->unsigned(),
            'application_no' => $this->string(50),
            'stu_title' => $this->string(15),
            'stu_first_name' => $this->string(150),
            'stu_middle_name' => $this->string(150),
            'stu_last_name' => $this->string(150),
            'stu_gender' => $this->string(20),
            'stu_dob' => $this->date(),
            'stu_birthplace' => $this->string(100),
            'stu_religion' => $this->string(50),
            'stu_aadhaar' => $this->string(12),
            'stu_mobile_no' => $this->string(15),
            'stu_whatsapp_no' => $this->string(15),
            'stu_email_id' => $this->string(150),
            'stu_admission_date' => $this->date(),
            'father_name' => $this->string(100),
            'father_mobile' => $this->string(15),
            'father_whatsapp' => $this->string(15),
            'father_email' => $this->string(150),
            'father_occupation' => $this->string(100),
            'mother_name' => $this->string(100),
            'mother_mobile' => $this->string(15),
            'mother_whatsapp' => $this->string(15),
            'mother_email' => $this->string(150),
            'mother_occupation' => $this->string(100),
            'guardian_name' => $this->string(100),
            'guardian_relation' => $this->string(30),
            'guardian_mobile' => $this->string(15),
            'address_line' => $this->string(255),
            'city' => $this->string(80),
            'state' => $this->string(80),
            'pincode' => $this->string(10),
            'prev_school_name' => $this->string(150),
            'prev_class' => $this->string(30),
            'emergency_contact_name' => $this->string(100),
            'emergency_contact_phone' => $this->string(15),
        ], $options);

        $this->createIndex('uq_stu_info_unique_id', 'stu_info', 'stu_unique_id', true);
        $this->createIndex('uq_stu_info_aadhaar', 'stu_info', 'stu_aadhaar', true);
        $this->createIndex('uq_stu_info_email', 'stu_info', 'stu_email_id', true);
        $this->createIndex('idx_stu_info_master', 'stu_info', 'stu_info_stu_master_id');
        $this->createIndex('idx_stu_info_name', 'stu_info', ['stu_first_name', 'stu_last_name']);
        $this->createIndex('idx_stu_info_mobile', 'stu_info', 'stu_mobile_no');
        $this->createIndex('idx_stu_info_dob', 'stu_info', 'stu_dob');

        $this->createTable('stu_master', [
            'stu_master_id' => $this->primaryKey()->unsigned(),
            'stu_master_stu_info_id' => $this->integer()->unsigned(),
            'stu_master_user_id' => $this->integer()->unsigned(),
            'stu_master_course_id' => $this->integer()->unsigned(),
            'stu_master_batch_id' => $this->integer()->unsigned(),
            'stu_master_section_id' => $this->integer()->unsigned(),
            'created_at' => $this->dateTime(),
            'created_by' => $this->integer()->unsigned(),
            'updated_at' => $this->dateTime(),
            'updated_by' => $this->integer()->unsigned(),
            'is_status' => $this->smallInteger()->notNull()->defaultValue(1),
        ], $options);

        $this->createIndex('idx_stu_master_info', 'stu_master', 'stu_master_stu_info_id');
        $this->createIndex('idx_stu_master_user', 'stu_master', 'stu_master_user_id');
        $this->createIndex('idx_stu_master_course', 'stu_master', 'stu_master_course_id');
        $this->createIndex('idx_stu_master_batch', 'stu_master', 'stu_master_batch_id');
        $this->createIndex('idx_stu_master_section', 'stu_master', 'stu_master_section_id');
        $this->createIndex('idx_stu_master_status', 'stu_master', 'is_status');
    }

    public function safeDown()
    {
        $this->dropTable('stu_master');
        $this->dropTable('stu_info');
    }
}
