<?php

namespace app\modules\exam\models;

use app\models\User;
use app\modules\student\models\StuMaster;
use yii\db\ActiveRecord;

/**
 * Tracks a student's attempt on an Exam.
 *
 * @property int $id
 * @property int $exam_id
 * @property int $user_id
 * @property int|null $stu_master_id
 * @property string|null $question_order
 * @property string $started_at
 * @property string|null $submitted_at
 * @property int $duration_used_sec
 * @property int $total_questions
 * @property int $answered_count
 * @property int $correct_count
 * @property float $total_marks
 * @property float $obtained_marks
 * @property float $percentage
 * @property int|null $is_passed
 * @property string $status
 */
class ExamAttempt extends ActiveRecord
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUBMITTED = 'submitted';

    public static function tableName()
    {
        return 'exam_attempts';
    }

    public function rules()
    {
        return [
            [['exam_id', 'user_id', 'started_at'], 'required'],
            [['exam_id', 'user_id', 'stu_master_id', 'duration_used_sec', 'total_questions', 'answered_count', 'correct_count'], 'integer'],
            [['total_marks', 'obtained_marks', 'percentage'], 'number'],
            [['is_passed'], 'boolean'],
            [['question_order', 'started_at', 'submitted_at'], 'safe'],
            [['status'], 'in', 'range' => [self::STATUS_IN_PROGRESS, self::STATUS_SUBMITTED]],
            [['status'], 'default', 'value' => self::STATUS_IN_PROGRESS],
            [['exam_id', 'user_id'], 'unique', 'targetAttribute' => ['exam_id', 'user_id']],
        ];
    }

    public function getExam()
    {
        return $this->hasOne(Exam::class, ['id' => 'exam_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getStudent()
    {
        return $this->hasOne(StuMaster::class, ['stu_master_id' => 'stu_master_id']);
    }

    public function getAnswers()
    {
        return $this->hasMany(ExamAttemptAnswer::class, ['attempt_id' => 'id'])->indexBy('question_id');
    }

    /** Returns ordered list of question IDs for this attempt */
    public function getOrderedQuestionIds(): array
    {
        if ($this->question_order) {
            $decoded = json_decode($this->question_order, true);
            if (is_array($decoded) && !empty($decoded)) {
                return array_map('intval', $decoded);
            }
        }
        return array_map('intval', ExamQuestionMap::find()
            ->select('question_id')
            ->where(['exam_id' => $this->exam_id])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->column());
    }

    /**
     * Remaining seconds for timed exams (also respects exam end_time).
     * Returns null if untimed (no duration_minutes and no end_time).
     */
    public function getRemainingSeconds(): ?int
    {
        $exam = $this->exam;
        if (!$exam) {
            return null;
        }

        $deadlines = [];
        if ($exam->duration_minutes && (int) $exam->duration_minutes > 0) {
            $deadlines[] = strtotime($this->started_at) + ((int) $exam->duration_minutes * 60);
        }
        if ($exam->end_time) {
            $deadlines[] = strtotime($exam->end_time);
        }

        if (empty($deadlines)) {
            return null;
        }

        $deadline = min($deadlines);
        return max(0, $deadline - time());
    }

    public function isTimeExpired(): bool
    {
        $remaining = $this->getRemainingSeconds();
        return $remaining !== null && $remaining <= 0;
    }

    /**
     * Grades all saved answers server-side, updates totals, and marks attempt as submitted.
     */
    public function gradeAndSubmit(): bool
    {
        if ($this->status === self::STATUS_SUBMITTED) {
            return true;
        }

        $questionIds = $this->getOrderedQuestionIds();
        $questions = ExamQuestion::find()
            ->where(['id' => $questionIds])
            ->indexBy('id')
            ->all();

        $answers = ExamAttemptAnswer::find()
            ->where(['attempt_id' => $this->id])
            ->indexBy('question_id')
            ->all();

        $totalMarks = 0.0;
        $obtainedMarks = 0.0;
        $answeredCount = 0;
        $correctCount = 0;

        foreach ($questionIds as $qid) {
            if (!isset($questions[$qid])) {
                continue;
            }
            $q = $questions[$qid];
            $qMarks = (float) $q->marks;
            $totalMarks += $qMarks;

            if (isset($answers[$qid]) && $answers[$qid]->selected_option !== null && $answers[$qid]->selected_option !== '') {
                $ans = $answers[$qid];
                $answeredCount++;
                $isCorrect = strtoupper((string) $ans->selected_option) === strtoupper((string) $q->correct_option);
                $ans->is_correct = $isCorrect ? 1 : 0;
                $ans->marks_awarded = $isCorrect ? $qMarks : 0.0;
                $ans->save(false);

                if ($isCorrect) {
                    $correctCount++;
                    $obtainedMarks += $qMarks;
                }
            }
        }

        $now = time();
        $elapsed = max(0, $now - strtotime($this->started_at));
        if ($this->exam && $this->exam->duration_minutes) {
            $elapsed = min($elapsed, (int) $this->exam->duration_minutes * 60);
        }

        $percentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0.0;

        $this->submitted_at = date('Y-m-d H:i:s', $now);
        $this->duration_used_sec = $elapsed;
        $this->total_questions = count($questions);
        $this->answered_count = $answeredCount;
        $this->correct_count = $correctCount;
        $this->total_marks = $totalMarks;
        $this->obtained_marks = $obtainedMarks;
        $this->percentage = $percentage;

        if ($this->exam && $this->exam->pass_percentage !== null && $this->exam->pass_percentage !== '') {
            $this->is_passed = ($percentage >= (float) $this->exam->pass_percentage) ? 1 : 0;
        } else {
            $this->is_passed = null;
        }

        $this->status = self::STATUS_SUBMITTED;
        return $this->save(false);
    }

    public function getStudentDisplayName(): string
    {
        if ($this->student && $this->student->info && $this->student->info->fullName) {
            return $this->student->info->fullName;
        }
        if ($this->user) {
            return $this->user->username;
        }
        return 'User #' . $this->user_id;
    }

    public function getFormattedDuration(): string
    {
        $sec = (int) $this->duration_used_sec;
        $mins = (int) floor($sec / 60);
        $rem = $sec % 60;
        return sprintf('%02dm %02ds', $mins, $rem);
    }
}
