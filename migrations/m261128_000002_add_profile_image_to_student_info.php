<?php

use yii\db\Migration;

class m261128_000002_add_profile_image_to_student_info extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%stu_info}}', true) === null) {
            return;
        }

        if ($this->db->schema->getTableSchema('{{%stu_info}}', true)->getColumn('Profile_image') === null) {
            $this->addColumn('{{%stu_info}}', 'Profile_image', $this->string(255));
        }
    }

    public function safeDown()
    {
        $schema = $this->db->schema->getTableSchema('{{%stu_info}}', true);
        if ($schema !== null && $schema->getColumn('Profile_image') !== null) {
            $this->dropColumn('{{%stu_info}}', 'Profile_image');
        }
    }
}
