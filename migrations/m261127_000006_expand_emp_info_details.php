<?php

use yii\db\Expression;
use yii\db\Migration;
use yii\db\Query;

class m261127_000006_expand_emp_info_details extends Migration
{
    public function safeUp()
    {
        $columns = [
            'emp_application_no' => $this->string(50),
            'emp_title' => $this->string(15),
            'emp_first_name' => $this->string(150),
            'emp_middle_name' => $this->string(150),
            'emp_last_name' => $this->string(150),
            'emp_gender' => $this->string(20),
            'emp_dob' => $this->date(),
            'emp_birthplace' => $this->string(100),
            'emp_religion' => $this->string(50),
            'emp_aadhaar' => $this->string(12),
            'emp_mobile_no' => $this->string(30),
            'emp_whatsapp_no' => $this->string(15),
            'emp_email_id' => $this->string(150),
            'emp_joining_date' => $this->date(),
            'emp_father_name' => $this->string(100),
            'emp_father_mobile' => $this->string(15),
            'emp_father_whatsapp' => $this->string(15),
            'emp_father_email' => $this->string(150),
            'emp_father_occupation' => $this->string(100),
            'emp_mother_name' => $this->string(100),
            'emp_mother_mobile' => $this->string(15),
            'emp_mother_whatsapp' => $this->string(15),
            'emp_mother_email' => $this->string(150),
            'emp_mother_occupation' => $this->string(100),
            'emp_guardian_name' => $this->string(100),
            'emp_guardian_relation' => $this->string(30),
            'emp_guardian_mobile' => $this->string(15),
            'address_line' => $this->string(255),
            'city' => $this->string(80),
            'state' => $this->string(80),
            'pincode' => $this->string(10),
            'previous_employer_name' => $this->string(150),
            'previous_designation' => $this->string(100),
            'emergency_contact_name' => $this->string(100),
            'emergency_contact_phone' => $this->string(15),
        ];
        foreach ($columns as $name => $definition) {
            $this->addColumn('{{%emp_info}}', $name, $definition);
        }

        $this->update('{{%emp_info}}', [
            'emp_first_name' => new Expression('name'),
            'emp_email_id' => new Expression('email'),
            'emp_mobile_no' => new Expression('phone'),
            'emp_joining_date' => new Expression('joining_date'),
        ]);
        $this->createIndex('idx-emp-info-name', '{{%emp_info}}', ['emp_first_name', 'emp_last_name']);
        $this->createIndex('idx-emp-info-mobile', '{{%emp_info}}', 'emp_mobile_no');
        $this->createIndex('idx-emp-info-dob', '{{%emp_info}}', 'emp_dob');

        $this->dropColumnPortable('{{%emp_info}}', 'name');
        $this->dropColumnPortable('{{%emp_info}}', 'email');
        $this->dropColumnPortable('{{%emp_info}}', 'phone');
        $this->dropColumnPortable('{{%emp_info}}', 'joining_date');
    }

    public function safeDown()
    {
        $this->addColumn('{{%emp_info}}', 'name', $this->string(150));
        $this->addColumn('{{%emp_info}}', 'email', $this->string(150));
        $this->addColumn('{{%emp_info}}', 'phone', $this->string(30));
        $this->addColumn('{{%emp_info}}', 'joining_date', $this->date());

        $records = (new Query())
            ->select([
                'emp_info_id',
                'emp_first_name',
                'emp_middle_name',
                'emp_last_name',
                'emp_email_id',
                'emp_mobile_no',
                'emp_joining_date',
            ])
            ->from('{{%emp_info}}')
            ->all($this->db);
        foreach ($records as $record) {
            $name = implode(' ', array_filter([
                $record['emp_first_name'],
                $record['emp_middle_name'],
                $record['emp_last_name'],
            ], static function ($part) {
                return $part !== null && $part !== '';
            }));
            $this->update(
                '{{%emp_info}}',
                [
                    'name' => $name === '' ? null : $name,
                    'email' => $record['emp_email_id'],
                    'phone' => $record['emp_mobile_no'],
                    'joining_date' => $record['emp_joining_date'],
                ],
                ['emp_info_id' => $record['emp_info_id']]
            );
        }

        $this->dropIndex('idx-emp-info-name', '{{%emp_info}}');
        $this->dropIndex('idx-emp-info-mobile', '{{%emp_info}}');
        $this->dropIndex('idx-emp-info-dob', '{{%emp_info}}');
        foreach (array_keys($this->detailColumns()) as $name) {
            $this->dropColumnPortable('{{%emp_info}}', $name);
        }
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

    private function detailColumns()
    {
        return [
            'emp_application_no' => true,
            'emp_title' => true,
            'emp_first_name' => true,
            'emp_middle_name' => true,
            'emp_last_name' => true,
            'emp_gender' => true,
            'emp_dob' => true,
            'emp_birthplace' => true,
            'emp_religion' => true,
            'emp_aadhaar' => true,
            'emp_mobile_no' => true,
            'emp_whatsapp_no' => true,
            'emp_email_id' => true,
            'emp_joining_date' => true,
            'emp_father_name' => true,
            'emp_father_mobile' => true,
            'emp_father_whatsapp' => true,
            'emp_father_email' => true,
            'emp_father_occupation' => true,
            'emp_mother_name' => true,
            'emp_mother_mobile' => true,
            'emp_mother_whatsapp' => true,
            'emp_mother_email' => true,
            'emp_mother_occupation' => true,
            'emp_guardian_name' => true,
            'emp_guardian_relation' => true,
            'emp_guardian_mobile' => true,
            'address_line' => true,
            'city' => true,
            'state' => true,
            'pincode' => true,
            'previous_employer_name' => true,
            'previous_designation' => true,
            'emergency_contact_name' => true,
            'emergency_contact_phone' => true,
        ];
    }
}
