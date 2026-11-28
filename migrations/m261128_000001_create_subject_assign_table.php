<?php

use yii\db\Migration;

class m261128_000001_create_subject_assign_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%subject_assign}}', [
            'id' => $this->primaryKey(),
            'subject_code' => $this->string(50)->notNull(),
            'course_id' => $this->integer()->notNull(),
            'Batch_id' => $this->integer()->notNull(),
            'section_id' => $this->integer()->notNull(),
            'employ_master_id' => $this->integer()->unsigned()->notNull(),
            'created_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-subject-assign-course', '{{%subject_assign}}', 'course_id');
        $this->createIndex('idx-subject-assign-batch', '{{%subject_assign}}', 'Batch_id');
        $this->createIndex('idx-subject-assign-section', '{{%subject_assign}}', 'section_id');
        $this->createIndex('idx-subject-assign-employee', '{{%subject_assign}}', 'employ_master_id');
        $this->createIndex(
            'uq-subject-assign-placement',
            '{{%subject_assign}}',
            ['subject_code', 'course_id', 'Batch_id', 'section_id'],
            true
        );

        $this->addForeignKey(
            'fk-subject-assign-course',
            '{{%subject_assign}}',
            'course_id',
            '{{%courses}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-subject-assign-batch',
            '{{%subject_assign}}',
            'Batch_id',
            '{{%batches}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-subject-assign-section',
            '{{%subject_assign}}',
            'section_id',
            '{{%sections}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-subject-assign-employee',
            '{{%subject_assign}}',
            'employ_master_id',
            '{{%emp_master}}',
            'emp_master_id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-subject-assign-employee', '{{%subject_assign}}');
        $this->dropForeignKey('fk-subject-assign-section', '{{%subject_assign}}');
        $this->dropForeignKey('fk-subject-assign-batch', '{{%subject_assign}}');
        $this->dropForeignKey('fk-subject-assign-course', '{{%subject_assign}}');
        $this->dropTable('{{%subject_assign}}');
    }
}
