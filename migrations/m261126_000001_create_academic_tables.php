<?php

use yii\db\Migration;

class m261126_000001_create_academic_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%courses}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150),
            'code' => $this->string(50),
            'description' => $this->text(),
            'fee_amount' => $this->decimal(12, 2),
            'active' => $this->boolean(),
            'created_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%subjects}}', [
            'id' => $this->primaryKey(),
            'course_id' => $this->integer(),
            'name' => $this->string(160),
            'code' => $this->string(50),
            'created_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->createIndex('idx-subjects-course_id', '{{%subjects}}', 'course_id');
        $this->addForeignKey('fk-subjects-course_id', '{{%subjects}}', 'course_id', '{{%courses}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%batches}}', [
            'id' => $this->primaryKey(),
            'course_id' => $this->integer(),
            'name' => $this->string(150),
            'start_date' => $this->date(),
            'end_date' => $this->date(),
            'capacity' => $this->integer(),
            'created_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->createIndex('idx-batches-course_id', '{{%batches}}', 'course_id');
        $this->addForeignKey('fk-batches-course_id', '{{%batches}}', 'course_id', '{{%courses}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%sections}}', [
            'id' => $this->primaryKey(),
            'course_id' => $this->integer(),
            'batch_id' => $this->integer(),
            'name' => $this->string(70),
            'created_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->createIndex('idx-sections-course_id', '{{%sections}}', 'course_id');
        $this->createIndex('idx-sections-batch_id', '{{%sections}}', 'batch_id');
        $this->addForeignKey('fk-sections-course_id', '{{%sections}}', 'course_id', '{{%courses}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk-sections-batch_id', '{{%sections}}', 'batch_id', '{{%batches}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-sections-batch_id', '{{%sections}}');
        $this->dropForeignKey('fk-sections-course_id', '{{%sections}}');
        $this->dropTable('{{%sections}}');
        $this->dropForeignKey('fk-batches-course_id', '{{%batches}}');
        $this->dropTable('{{%batches}}');
        $this->dropForeignKey('fk-subjects-course_id', '{{%subjects}}');
        $this->dropTable('{{%subjects}}');
        $this->dropTable('{{%courses}}');
    }
}
