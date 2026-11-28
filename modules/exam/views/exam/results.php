<?php

use app\modules\exam\models\ExamAttempt;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $exam app\modules\exam\models\Exam */
/* @var $attempts app\modules\exam\models\ExamAttempt[] */
/* @var $stats array */

$this->title = 'Results: ' . $exam->title;
$this->params['breadcrumbs'][] = ['label' => 'Online Exams', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $exam->title, 'url' => ['view', 'id' => $exam->id]];
$this->params['breadcrumbs'][] = 'Results';
?>
<div class="exam-results">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-chart-column text-success me-2"></i>Exam Results
            </h3>
            <p class="text-muted small mb-0"><?= Html::encode($exam->title) ?></p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-file-csv me-1"></i> Export CSV', ['export-results', 'id' => $exam->id], ['class' => 'btn btn-outline-success']) ?>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Exam', ['view', 'id' => $exam->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-3">
                <div class="text-muted small">Total Attempts</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= $stats['total'] ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-3">
                <div class="text-muted small">Submitted</div>
                <div class="fs-4 fw-bold text-primary mt-1"><?= $stats['submitted'] ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-3">
                <div class="text-muted small">Average Score</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= $stats['avg_percentage'] ?>%</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-3">
                <div class="text-muted small">Passed</div>
                <div class="fs-4 fw-bold text-success mt-1">
                    <?= $exam->pass_percentage !== null ? ($stats['passed'] . ' / ' . $stats['submitted']) : 'N/A' ?>
                </div>
            </div></div>
        </div>
    </div>

    <!-- Attempts Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <?php if (empty($attempts)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-user-clock fa-2x mb-2 d-block"></i>
                    No students have attempted this exam yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th class="px-3">#</th>
                                <th>Student</th>
                                <th>Status</th>
                                <th class="text-center">Answered</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Percentage</th>
                                <th class="text-center">Result</th>
                                <th>Time Taken</th>
                                <th>Submitted At</th>
                                <th class="text-end px-3">Sheet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attempts as $i => $a): ?>
                                <tr>
                                    <td class="px-3 text-muted"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= Html::encode($a->getStudentDisplayName()) ?></div>
                                        <?php if ($a->user): ?><small class="text-muted">@<?= Html::encode($a->user->username) ?></small><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($a->status === ExamAttempt::STATUS_SUBMITTED): ?>
                                            <span class="badge bg-success-subtle text-success">Submitted</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning">In Progress</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= $a->answered_count ?> / <?= $a->total_questions ?></td>
                                    <td class="text-center fw-semibold">
                                        <?= rtrim(rtrim(number_format((float) $a->obtained_marks, 2), '0'), '.') ?> / <?= rtrim(rtrim(number_format((float) $a->total_marks, 2), '0'), '.') ?>
                                    </td>
                                    <td class="text-center"><?= number_format((float) $a->percentage, 1) ?>%</td>
                                    <td class="text-center">
                                        <?php if ($a->status !== ExamAttempt::STATUS_SUBMITTED): ?>
                                            <span class="text-muted">—</span>
                                        <?php elseif ($a->is_passed === null): ?>
                                            <span class="badge bg-light text-dark border">Graded</span>
                                        <?php elseif ($a->is_passed): ?>
                                            <span class="badge bg-success">PASS</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">FAIL</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= Html::encode($a->getFormattedDuration()) ?></td>
                                    <td class="small text-muted"><?= $a->submitted_at ? Yii::$app->formatter->asDatetime($a->submitted_at) : '—' ?></td>
                                    <td class="text-end px-3">
                                        <?= Html::a('<i class="fa-solid fa-eye me-1"></i> Review', ['attempt-view', 'attempt_id' => $a->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
