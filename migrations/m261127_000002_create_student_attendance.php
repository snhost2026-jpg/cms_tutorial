<?php

use yii\db\Migration;

class m261127_000002_create_student_attendance extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%student_attendance}}', [
            'id' => $this->primaryKey(),
            'student_id' => $this->integer()->unsigned()->notNull(),
            'section_id' => $this->integer()->notNull(),
            'subject_id' => $this->integer()->notNull(),
            'attendance_date' => $this->date()->notNull(),
            'status' => $this->string(10)->notNull(),
            'marked_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            'uq-student-attendance-session-student',
            '{{%student_attendance}}',
            ['attendance_date', 'section_id', 'subject_id', 'student_id'],
            true
        );
        $this->createIndex(
            'idx-student-attendance-student-date',
            '{{%student_attendance}}',
            ['student_id', 'attendance_date']
        );
        $this->addForeignKey(
            'fk-student-attendance-student',
            '{{%student_attendance}}',
            'student_id',
            '{{%stu_master}}',
            'stu_master_id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-student-attendance-section',
            '{{%student_attendance}}',
            'section_id',
            '{{%sections}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-student-attendance-subject',
            '{{%student_attendance}}',
            'subject_id',
            '{{%subjects}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%student_attendance}}');
    }
}
