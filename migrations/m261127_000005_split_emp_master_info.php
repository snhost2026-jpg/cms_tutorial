<?php

use yii\db\Migration;
use yii\db\Query;

class m261127_000005_split_emp_master_info extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%emp_master}}', [
            'emp_master_id' => $this->primaryKey()->unsigned(),
            'emp_master_emp_info_id' => $this->integer()->unsigned(),
            'emp_master_user_id' => $this->integer()->unsigned(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->unsigned(),
            'is_status' => $this->smallInteger()->notNull()->defaultValue(1),
        ], $tableOptions);
        $this->addColumn('{{%emp_info}}', 'emp_info_emp_master_id', $this->integer()->unsigned());

        $this->createIndex('uq-emp-master-info', '{{%emp_master}}', 'emp_master_emp_info_id', true);
        $this->createIndex('idx-emp-master-user', '{{%emp_master}}', 'emp_master_user_id');
        $this->createIndex('idx-emp-master-status', '{{%emp_master}}', 'is_status');
        $this->createIndex('uq-emp-info-master', '{{%emp_info}}', 'emp_info_emp_master_id', true);
        $this->createIndex('idx-emp-info-type', '{{%emp_info}}', 'employee_type');

        $employees = (new Query())
            ->select(['emp_info_id', 'status', 'created_at', 'updated_at'])
            ->from('{{%emp_info}}')
            ->all($this->db);

        foreach ($employees as $employee) {
            $employeeId = $employee['emp_info_id'];
            $this->insert('{{%emp_master}}', [
                'emp_master_id' => $employeeId,
                'emp_master_emp_info_id' => $employeeId,
                'created_at' => $employee['created_at'],
                'updated_at' => $employee['updated_at'],
                'is_status' => $employee['status'],
            ]);
            $this->update(
                '{{%emp_info}}',
                ['emp_info_emp_master_id' => $employeeId],
                ['emp_info_id' => $employeeId]
            );
        }

        $this->dropIndex('idx-emp-info-type-status', '{{%emp_info}}');
        $this->dropColumnPortable('{{%emp_info}}', 'status');
        $this->dropColumnPortable('{{%emp_info}}', 'created_at');
        $this->dropColumnPortable('{{%emp_info}}', 'updated_at');

        if ($this->db->schema->getTableSchema('{{%employees}}', true) !== null) {
            $this->dropTable('{{%employees}}');
        }
    }

    public function safeDown()
    {
        $this->addColumn('{{%emp_info}}', 'status', $this->smallInteger()->notNull()->defaultValue(1));
        $this->addColumn('{{%emp_info}}', 'created_at', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%emp_info}}', 'updated_at', $this->integer()->notNull()->defaultValue(0));

        $employees = (new Query())
            ->select(['emp_master_emp_info_id', 'is_status', 'created_at', 'updated_at'])
            ->from('{{%emp_master}}')
            ->all($this->db);
        foreach ($employees as $employee) {
            $this->update(
                '{{%emp_info}}',
                [
                    'status' => $employee['is_status'],
                    'created_at' => $employee['created_at'],
                    'updated_at' => $employee['updated_at'],
                ],
                ['emp_info_id' => $employee['emp_master_emp_info_id']]
            );
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%employees}}', [
            'id' => $this->primaryKey(),
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
        $this->createIndex('uq-employees-employee-code', '{{%employees}}', 'employee_code', true);
        $this->createIndex('idx-employees-type-status', '{{%employees}}', ['employee_type', 'status']);

        $records = (new Query())
            ->select([
                'em.emp_master_id',
                'ei.employee_code',
                'ei.name',
                'ei.employee_type',
                'ei.email',
                'ei.phone',
                'ei.designation',
                'ei.department',
                'ei.joining_date',
                'em.is_status',
                'em.created_at',
                'em.updated_at',
            ])
            ->from(['em' => '{{%emp_master}}'])
            ->innerJoin(['ei' => '{{%emp_info}}'], 'ei.emp_info_id = em.emp_master_emp_info_id')
            ->all($this->db);
        foreach ($records as $record) {
            $this->insert('{{%employees}}', [
                'id' => $record['emp_master_id'],
                'employee_code' => $record['employee_code'],
                'name' => $record['name'],
                'employee_type' => $record['employee_type'],
                'email' => $record['email'],
                'phone' => $record['phone'],
                'designation' => $record['designation'],
                'department' => $record['department'],
                'joining_date' => $record['joining_date'],
                'status' => $record['is_status'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at'],
            ]);
        }

        $this->dropIndex('idx-emp-info-type', '{{%emp_info}}');
        $this->dropIndex('uq-emp-info-master', '{{%emp_info}}');
        $this->dropColumnPortable('{{%emp_info}}', 'emp_info_emp_master_id');
        $this->dropTable('{{%emp_master}}');
        $this->createIndex('idx-emp-info-type-status', '{{%emp_info}}', ['employee_type', 'status']);
    }

    private function dropColumnPortable($table, $column)
    {
        if ($this->db->driverName === 'sqlite') {
            $tableName = $this->db->schema->getRawTableName($table);
            $this->db->createCommand(
                'ALTER TABLE ' . $this->db->quoteTableName($tableName)
                . ' DROP COLUMN ' . $this->db->quoteColumnName($column)
            )->execute();
            $this->db->schema->refreshTableSchema($tableName);
            return;
        }

        $this->dropColumn($table, $column);
    }
}
