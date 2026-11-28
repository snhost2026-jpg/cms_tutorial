<?php

use yii\db\Migration;
use yii\db\Query;

class m261127_000004_create_emp_info_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%emp_info}}', [
            'emp_info_id' => $this->primaryKey()->unsigned(),
            'employee_code' => $this->string(32),
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

        $this->createIndex('uq-emp-info-employee-code', '{{%emp_info}}', 'employee_code', true);
        $this->createIndex('idx-emp-info-type-status', '{{%emp_info}}', ['employee_type', 'status']);

        if ($this->db->schema->getTableSchema('{{%employees}}', true) !== null) {
            $employees = (new Query())
                ->select([
                    'id',
                    'employee_code',
                    'name',
                    'employee_type',
                    'email',
                    'phone',
                    'designation',
                    'department',
                    'joining_date',
                    'status',
                    'created_at',
                    'updated_at',
                ])
                ->from('{{%employees}}')
                ->all($this->db);

            $rows = [];
            foreach ($employees as $employee) {
                $rows[] = [
                    $employee['id'],
                    $employee['employee_code'],
                    $employee['name'],
                    $employee['employee_type'],
                    $employee['email'],
                    $employee['phone'],
                    $employee['designation'],
                    $employee['department'],
                    $employee['joining_date'],
                    $employee['status'],
                    $employee['created_at'],
                    $employee['updated_at'],
                ];
            }

            if ($rows !== []) {
                $this->batchInsert(
                    '{{%emp_info}}',
                    [
                        'emp_info_id',
                        'employee_code',
                        'name',
                        'employee_type',
                        'email',
                        'phone',
                        'designation',
                        'department',
                        'joining_date',
                        'status',
                        'created_at',
                        'updated_at',
                    ],
                    $rows
                );
            }
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%emp_info}}');
    }
}
