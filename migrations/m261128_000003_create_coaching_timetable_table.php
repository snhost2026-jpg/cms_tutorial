<?php

use yii\db\Migration;

class m261128_000003_create_coaching_timetable_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('coaching_timetable', true) === null) {
            if ($this->db->driverName === 'mysql') {
                $this->execute("
                    CREATE TABLE coaching_timetable (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

                        academic_session_id INT NOT NULL,
                        course_id INT NOT NULL,
                        batch_id INT NOT NULL,
                        section_id INT DEFAULT NULL,
                        subject_id INT NOT NULL,
                        employee_id INT NOT NULL,

                        day_of_week TINYINT NOT NULL
                            COMMENT '1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday, 7=Sunday',

                        start_time TIME NOT NULL,
                        end_time TIME NOT NULL,

                        room_id INT DEFAULT NULL,

                        class_type ENUM(
                            'Theory',
                            'Practical',
                            'Revision',
                            'Test',
                            'Doubt Clearing'
                        ) DEFAULT 'Theory',

                        effective_from DATE NOT NULL,
                        effective_to DATE DEFAULT NULL,

                        status TINYINT NOT NULL DEFAULT 1
                            COMMENT '1=Active, 0=Inactive',

                        created_by INT DEFAULT NULL,
                        updated_by INT DEFAULT NULL,

                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,

                        CHECK (day_of_week BETWEEN 1 AND 7),
                        CHECK (start_time < end_time),
                        CHECK (effective_to IS NULL OR effective_to >= effective_from),

                        INDEX idx_batch_day (batch_id, day_of_week),
                        INDEX idx_employee_day (employee_id, day_of_week)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            } else {
                $this->createTable('coaching_timetable', [
                    'id' => $this->bigPrimaryKey()->unsigned(),
                    'academic_session_id' => $this->integer()->notNull(),
                    'course_id' => $this->integer()->notNull(),
                    'batch_id' => $this->integer()->notNull(),
                    'section_id' => $this->integer()->defaultValue(null),
                    'subject_id' => $this->integer()->notNull(),
                    'employee_id' => $this->integer()->notNull(),
                    'day_of_week' => $this->tinyInteger()->notNull(),
                    'start_time' => $this->time()->notNull(),
                    'end_time' => $this->time()->notNull(),
                    'room_id' => $this->integer()->defaultValue(null),
                    'class_type' => $this->string(30)->defaultValue('Theory'),
                    'effective_from' => $this->date()->notNull(),
                    'effective_to' => $this->date()->defaultValue(null),
                    'status' => $this->tinyInteger()->notNull()->defaultValue(1),
                    'created_by' => $this->integer()->defaultValue(null),
                    'updated_by' => $this->integer()->defaultValue(null),
                    'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                    'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                ]);

                $this->createIndex('idx_batch_day', 'coaching_timetable', ['batch_id', 'day_of_week']);
                $this->createIndex('idx_employee_day', 'coaching_timetable', ['employee_id', 'day_of_week']);
            }
        }

        // Register RBAC route permission if RBAC tables are present
        if ($this->db->schema->getTableSchema('auth_item', true) !== null) {
            $now = time();
            $items = [
                '/timetable/*' => 'Timetable module full route access',
                'manageTimetable' => 'Manage coaching weekly timetable capability',
            ];
            foreach ($items as $name => $desc) {
                $exists = (new \yii\db\Query())->from('auth_item')->where(['name' => $name])->exists($this->db);
                if (!$exists) {
                    $this->insert('auth_item', [
                        'name' => $name,
                        'type' => 2,
                        'description' => $desc,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            if ($this->db->schema->getTableSchema('auth_item_child', true) !== null) {
                $links = [
                    ['manager', '/timetable/*'],
                    ['manageTimetable', '/timetable/*'],
                ];
                foreach ($links as [$parent, $child]) {
                    $parentExists = (new \yii\db\Query())->from('auth_item')->where(['name' => $parent])->exists($this->db);
                    $linkExists = (new \yii\db\Query())
                        ->from('auth_item_child')
                        ->where(['parent' => $parent, 'child' => $child])
                        ->exists($this->db);
                    if ($parentExists && !$linkExists) {
                        $this->insert('auth_item_child', [
                            'parent' => $parent,
                            'child' => $child,
                        ]);
                    }
                }
            }
        }
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('coaching_timetable', true) !== null) {
            $this->dropTable('coaching_timetable');
        }
    }
}
