<?php

use yii\db\Migration;

/**
 * Handles creation of RBAC tables (`auth_rule`, `auth_item`, `auth_item_child`, `auth_assignment`) and `menu`.
 */
class m240101_000002_create_rbac_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // auth_rule
        if ($this->db->getTableSchema('auth_rule', true) === null) {
            $this->createTable('{{%auth_rule}}', [
                'name' => $this->string(64)->notNull(),
                'data' => $this->binary(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
                'PRIMARY KEY ([[name]])',
            ], $tableOptions);
        }

        // auth_item
        if ($this->db->getTableSchema('auth_item', true) === null) {
            $this->createTable('{{%auth_item}}', [
                'name' => $this->string(64)->notNull(),
                'type' => $this->smallInteger()->notNull(),
                'description' => $this->text(),
                'rule_name' => $this->string(64),
                'data' => $this->binary(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
                'PRIMARY KEY ([[name]])',
            ], $tableOptions);

            $this->createIndex('idx-auth_item-type', '{{%auth_item}}', 'type');
            $this->addForeignKey('fk-auth_item-rule_name', '{{%auth_item}}', 'rule_name', '{{%auth_rule}}', 'name', 'SET NULL', 'CASCADE');
        }

        // auth_item_child
        if ($this->db->getTableSchema('auth_item_child', true) === null) {
            $this->createTable('{{%auth_item_child}}', [
                'parent' => $this->string(64)->notNull(),
                'child' => $this->string(64)->notNull(),
                'PRIMARY KEY ([[parent]], [[child]])',
            ], $tableOptions);

            $this->addForeignKey('fk-auth_item_child-parent', '{{%auth_item_child}}', 'parent', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
            $this->addForeignKey('fk-auth_item_child-child', '{{%auth_item_child}}', 'child', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
        }

        // auth_assignment
        if ($this->db->getTableSchema('auth_assignment', true) === null) {
            $this->createTable('{{%auth_assignment}}', [
                'item_name' => $this->string(64)->notNull(),
                'user_id' => $this->string(64)->notNull(),
                'created_at' => $this->integer(),
                'PRIMARY KEY ([[item_name]], [[user_id]])',
            ], $tableOptions);

            $this->createIndex('idx-auth_assignment-user_id', '{{%auth_assignment}}', 'user_id');
            $this->addForeignKey('fk-auth_assignment-item_name', '{{%auth_assignment}}', 'item_name', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
        }

        // menu (yii2-admin)
        if ($this->db->getTableSchema('menu', true) === null) {
            $this->createTable('{{%menu}}', [
                'id' => $this->primaryKey(),
                'name' => $this->string(128)->notNull(),
                'parent' => $this->integer(),
                'route' => $this->string(),
                'order' => $this->integer(),
                'data' => $this->binary(),
            ], $tableOptions);

            $this->addForeignKey('fk-menu-parent', '{{%menu}}', 'parent', '{{%menu}}', 'id', 'SET NULL', 'CASCADE');
        }
    }

    public function safeDown()
    {
        if ($this->db->getTableSchema('menu', true) !== null) {
            $this->dropTable('{{%menu}}');
        }
        if ($this->db->getTableSchema('auth_assignment', true) !== null) {
            $this->dropTable('{{%auth_assignment}}');
        }
        if ($this->db->getTableSchema('auth_item_child', true) !== null) {
            $this->dropTable('{{%auth_item_child}}');
        }
        if ($this->db->getTableSchema('auth_item', true) !== null) {
            $this->dropTable('{{%auth_item}}');
        }
        if ($this->db->getTableSchema('auth_rule', true) !== null) {
            $this->dropTable('{{%auth_rule}}');
        }
    }
}
