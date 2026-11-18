<?php

use yii\db\Migration;

/**
 * Handles the creation of `user` and `user_login_history` tables.
 */
class m240101_000001_create_user_and_login_history extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // Create `user` table if not exists
        if ($this->db->getTableSchema('user', true) === null) {
            $this->createTable('{{%user}}', [
                'id' => $this->primaryKey(),
                'username' => $this->string(150)->notNull()->unique(),
                'auth_key' => $this->string(32)->notNull(),
                'password_hash' => $this->string(255)->notNull(),
                'password_reset_token' => $this->string(255)->null(),
                'email' => $this->string(150)->notNull(),
                'status' => $this->smallInteger()->notNull()->defaultValue(10),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
                'otp_code' => $this->string(10)->null(),
                'otp_expiry' => $this->dateTime()->null(),
                'otp_verified' => $this->smallInteger()->defaultValue(0),
            ], $tableOptions);
        }

        // Create `user_login_history` table if not exists
        if ($this->db->getTableSchema('user_login_history', true) === null) {
            $this->createTable('{{%user_login_history}}', [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer()->notNull(),
                'login_at' => $this->dateTime()->null(),
                'logout_at' => $this->dateTime()->null(),
                'ip_address' => $this->string(50)->null(),
                'user_agent' => $this->text()->null(),
                'location' => $this->string(255)->null(),
                'session_id' => $this->string(255)->null(),
                'is_active' => $this->smallInteger()->defaultValue(1),
                'logout_reason' => $this->string(255)->null(),
                'created_at' => $this->dateTime()->null(),
                'updated_at' => $this->dateTime()->null(),
            ], $tableOptions);

            $this->createIndex(
                'idx_user_login_history_user_id',
                '{{%user_login_history}}',
                'user_id'
            );

            $this->addForeignKey(
                'fk_user_login_history_user',
                '{{%user_login_history}}',
                'user_id',
                '{{%user}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }
    }

    public function safeDown()
    {
        if ($this->db->getTableSchema('user_login_history', true) !== null) {
            $this->dropTable('{{%user_login_history}}');
        }

        if ($this->db->getTableSchema('user', true) !== null) {
            $this->dropTable('{{%user}}');
        }
    }
}
