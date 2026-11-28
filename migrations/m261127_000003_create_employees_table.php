<?php

use yii\db\Migration;

class m261127_000003_create_employees_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%employees}}', [
            'id' => $this->primaryKey(),
            'employee_code' => $this->string(32)->notNull(),
            'name' => $this->string(150)->notNull(),
            'employee_type' => $this->string(20)->notNull(),
            'email' => $this->string(150),
            'phone' => $this->string(30),
            'designation' => $this->string(100),
            'department' => $this->string(100),
            'joining_date' => $this->date(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            'uq-employees-employee-code',
            '{{%employees}}',
            'employee_code',
            true
        );
        $this->createIndex(
            'idx-employees-type-status',
            '{{%employees}}',
            ['employee_type', 'status']
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%employees}}');
    }
}
