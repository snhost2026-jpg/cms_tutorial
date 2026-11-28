<?php

use yii\db\Migration;

/**
 * Creates tables for the Online Exam module:
 * exam_questions, exams, exam_question_map, exam_attempts, exam_attempt_answers.
 */
class m261128_000001_create_exam_tables extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            : null;

        if ($this->db->getTableSchema('exam_questions', true) === null) {
            $this->createTable('exam_questions', [
                'id' => $this->primaryKey()->unsigned(),
                'course_id' => $this->integer(),
                'subject_id' => $this->integer(),
                'question_text' => $this->text()->notNull(),
                'option_a' => $this->string(500)->notNull(),
                'option_b' => $this->string(500)->notNull(),
                'option_c' => $this->string(500),
                'option_d' => $this->string(500),
                'correct_option' => $this->char(1)->notNull(),
                'marks' => $this->decimal(6, 2)->notNull()->defaultValue(1.00),
                'difficulty' => $this->string(20)->notNull()->defaultValue('medium'),
                'is_active' => $this->boolean()->notNull()->defaultValue(1),
                'created_by' => $this->integer(),
                'created_at' => $this->dateTime(),
                'updated_by' => $this->integer(),
                'updated_at' => $this->dateTime(),
            ], $options);

            $this->createIndex('idx_exam_questions_course', 'exam_questions', 'course_id');
            $this->createIndex('idx_exam_questions_subject', 'exam_questions', 'subject_id');
            $this->createIndex('idx_exam_questions_difficulty', 'exam_questions', 'difficulty');
            $this->createIndex('idx_exam_questions_active', 'exam_questions', 'is_active');
        }

        if ($this->db->getTableSchema('exams', true) === null) {
            $this->createTable('exams', [
                'id' => $this->primaryKey()->unsigned(),
                'title' => $this->string(200)->notNull(),
                'description' => $this->text(),
                'course_id' => $this->integer(),
                'batch_id' => $this->integer(),
                'section_id' => $this->integer(),
                'subject_id' => $this->integer(),
                'duration_minutes' => $this->integer(),
                'start_time' => $this->dateTime(),
                'end_time' => $this->dateTime(),
                'pass_percentage' => $this->decimal(5, 2),
                'shuffle_questions' => $this->boolean()->notNull()->defaultValue(0),
                'show_result' => $this->boolean()->notNull()->defaultValue(1),
                'status' => $this->string(20)->notNull()->defaultValue('draft'),
                'created_by' => $this->integer(),
                'created_at' => $this->dateTime(),
                'updated_by' => $this->integer(),
                'updated_at' => $this->dateTime(),
            ], $options);

            $this->createIndex('idx_exams_course', 'exams', 'course_id');
            $this->createIndex('idx_exams_batch', 'exams', 'batch_id');
            $this->createIndex('idx_exams_section', 'exams', 'section_id');
            $this->createIndex('idx_exams_subject', 'exams', 'subject_id');
            $this->createIndex('idx_exams_status', 'exams', 'status');
        }

        if ($this->db->getTableSchema('exam_question_map', true) === null) {
            $this->createTable('exam_question_map', [
                'id' => $this->primaryKey()->unsigned(),
                'exam_id' => $this->integer()->unsigned()->notNull(),
                'question_id' => $this->integer()->unsigned()->notNull(),
                'sort_order' => $this->integer()->notNull()->defaultValue(0),
            ], $options);

            $this->createIndex('uq_exam_question_map', 'exam_question_map', ['exam_id', 'question_id'], true);
            $this->createIndex('idx_exam_question_map_exam', 'exam_question_map', 'exam_id');
            $this->createIndex('idx_exam_question_map_question', 'exam_question_map', 'question_id');
        }

        if ($this->db->getTableSchema('exam_attempts', true) === null) {
            $this->createTable('exam_attempts', [
                'id' => $this->primaryKey()->unsigned(),
                'exam_id' => $this->integer()->unsigned()->notNull(),
                'user_id' => $this->integer()->notNull(),
                'stu_master_id' => $this->integer()->unsigned(),
                'question_order' => $this->text(),
                'started_at' => $this->dateTime()->notNull(),
                'submitted_at' => $this->dateTime(),
                'duration_used_sec' => $this->integer()->notNull()->defaultValue(0),
                'total_questions' => $this->integer()->notNull()->defaultValue(0),
                'answered_count' => $this->integer()->notNull()->defaultValue(0),
                'correct_count' => $this->integer()->notNull()->defaultValue(0),
                'total_marks' => $this->decimal(8, 2)->notNull()->defaultValue(0.00),
                'obtained_marks' => $this->decimal(8, 2)->notNull()->defaultValue(0.00),
                'percentage' => $this->decimal(5, 2)->notNull()->defaultValue(0.00),
                'is_passed' => $this->boolean(),
                'status' => $this->string(20)->notNull()->defaultValue('in_progress'),
            ], $options);

            $this->createIndex('uq_exam_attempts_exam_user', 'exam_attempts', ['exam_id', 'user_id'], true);
            $this->createIndex('idx_exam_attempts_exam', 'exam_attempts', 'exam_id');
            $this->createIndex('idx_exam_attempts_user', 'exam_attempts', 'user_id');
            $this->createIndex('idx_exam_attempts_status', 'exam_attempts', 'status');
        }

        if ($this->db->getTableSchema('exam_attempt_answers', true) === null) {
            $this->createTable('exam_attempt_answers', [
                'id' => $this->primaryKey()->unsigned(),
                'attempt_id' => $this->integer()->unsigned()->notNull(),
                'question_id' => $this->integer()->unsigned()->notNull(),
                'selected_option' => $this->char(1),
                'is_marked_review' => $this->boolean()->notNull()->defaultValue(0),
                'is_correct' => $this->boolean(),
                'marks_awarded' => $this->decimal(6, 2)->notNull()->defaultValue(0.00),
                'updated_at' => $this->dateTime(),
            ], $options);

            $this->createIndex('uq_exam_attempt_answers', 'exam_attempt_answers', ['attempt_id', 'question_id'], true);
            $this->createIndex('idx_exam_attempt_answers_attempt', 'exam_attempt_answers', 'attempt_id');
        }
    }

    public function safeDown()
    {
        foreach (['exam_attempt_answers', 'exam_attempts', 'exam_question_map', 'exams', 'exam_questions'] as $table) {
            if ($this->db->getTableSchema($table, true) !== null) {
                $this->dropTable($table);
            }
        }
    }
}
