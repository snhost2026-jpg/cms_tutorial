<?php

use app\modules\exam\models\Exam;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\exam\models\Exam */
/* @var $assignedQuestions app\modules\exam\models\ExamQuestion[] */
/* @var $attemptCount int */
/* @var $submittedCount int */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Online Exams', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$totalMarks = 0;
foreach ($assignedQuestions as $q) {
    $totalMarks += (float) $q->marks;
}
?>
<div class="exam-view">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <h3 class="fw-bold text-dark mb-0"><?= Html::encode($model->title) ?></h3>
                <span class="badge <?= $model->getStatusBadgeClass() ?> px-2 py-1">
                    <?= Html::encode(Exam::statusOptions()[$model->status] ?? ucfirst($model->status)) ?>
                </span>
            </div>
            <p class="text-muted small mb-0">
                <?= $model->description ? Html::encode($model->description) : 'No custom description provided.' ?>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($model->status !== Exam::STATUS_PUBLISHED): ?>
                <?= Html::a('<i class="fa-solid fa-rocket me-1"></i> Publish', ['set-status', 'id' => $model->id, 'status' => Exam::STATUS_PUBLISHED], [
                    'class' => 'btn btn-success',
                    'data' => ['method' => 'post', 'confirm' => 'Publish this exam so eligible students can see and take it?'],
                ]) ?>
            <?php else: ?>
                <?= Html::a('<i class="fa-solid fa-lock me-1"></i> Close Exam', ['set-status', 'id' => $model->id, 'status' => Exam::STATUS_CLOSED], [
                    'class' => 'btn btn-outline-warning',
                    'data' => ['method' => 'post', 'confirm' => 'Close this exam? Students will no longer be able to start it.'],
                ]) ?>
            <?php endif; ?>
            <?= Html::a('<i class="fa-solid fa-list-check me-1"></i> Questions (' . count($assignedQuestions) . ')', ['questions', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-chart-column me-1"></i> Results (' . $submittedCount . ')', ['results', 'id' => $model->id], ['class' => 'btn btn-outline-success']) ?>
            <?= Html::a('<i class="fa-solid fa-pen me-1"></i> Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-dark']) ?>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <!-- Summary KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">Questions &amp; Marks</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= count($assignedQuestions) ?> Qs <span class="fs-6 text-muted fw-normal">/ <?= rtrim(rtrim(number_format($totalMarks, 2), '0'), '.') ?> marks</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">Duration</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= $model->duration_minutes ? ($model->duration_minutes . ' min') : 'Untimed' ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">Pass Percentage</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= $model->pass_percentage !== null ? (rtrim(rtrim(number_format((float) $model->pass_percentage, 2), '0'), '.') . '%') : 'None' ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">Student Attempts</div>
                    <div class="fs-4 fw-bold text-primary mt-1"><?= $submittedCount ?> <span class="fs-6 text-muted fw-normal">submitted / <?= $attemptCount ?> total</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Details -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-sliders text-primary me-2"></i>Exam Configuration</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3"><span class="text-muted small d-block">Course</span><strong><?= $model->course ? Html::encode($model->course->name) : 'All Courses' ?></strong></div>
                <div class="col-md-3"><span class="text-muted small d-block">Batch</span><strong><?= $model->batch ? Html::encode($model->batch->name) : 'All Batches' ?></strong></div>
                <div class="col-md-3"><span class="text-muted small d-block">Section</span><strong><?= $model->section ? Html::encode($model->section->name) : 'All Sections' ?></strong></div>
                <div class="col-md-3"><span class="text-muted small d-block">Subject</span><strong><?= $model->subject ? Html::encode($model->subject->name) : 'Any Subject' ?></strong></div>
                <div class="col-md-3"><span class="text-muted small d-block">Start Date &amp; Time</span><strong><?= $model->start_time ? Yii::$app->formatter->asDatetime($model->start_time) : 'No start restriction' ?></strong></div>
                <div class="col-md-3"><span class="text-muted small d-block">End Date &amp; Time</span><strong><?= $model->end_time ? Yii::$app->formatter->asDatetime($model->end_time) : 'No end restriction' ?></strong></div>
                <div class="col-md-3"><span class="text-muted small d-block">Shuffle Questions</span><strong><?= $model->shuffle_questions ? '<span class="text-success">Enabled</span>' : '<span class="text-muted">Disabled</span>' ?></strong></div>
                <div class="col-md-3"><span class="text-muted small d-block">Show Result Immediately</span><strong><?= $model->show_result ? '<span class="text-success">Enabled</span>' : '<span class="text-muted">Disabled</span>' ?></strong></div>
            </div>
        </div>
    </div>

    <!-- Assigned Questions Preview -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-ol text-success me-2"></i>Assigned Questions (<?= count($assignedQuestions) ?>)</h5>
            <?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i> Select / Add Questions', ['questions', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($assignedQuestions)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-circle-exclamation fa-2x mb-2 d-block"></i>
                    No questions assigned yet. Click <strong>Select / Add Questions</strong> before publishing.
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($assignedQuestions as $idx => $q): ?>
                        <div class="list-group-item p-3 px-4">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="fw-semibold text-dark">
                                    <span class="badge bg-light text-dark border me-2">Q<?= $idx + 1 ?></span>
                                    <?= Html::encode($q->question_text) ?>
                                </div>
                                <span class="badge bg-primary-subtle text-primary flex-shrink-0"><?= rtrim(rtrim(number_format((float) $q->marks, 2), '0'), '.') ?> mark(s)</span>
                            </div>
                            <div class="row g-2 mt-2 small">
                                <?php foreach ($q->getAvailableOptions() as $optKey => $optVal): ?>
                                    <div class="col-md-6">
                                        <span class="badge <?= $optKey === $q->correct_option ? 'bg-success' : 'bg-light text-dark border' ?> me-1"><?= $optKey ?></span>
                                        <span class="<?= $optKey === $q->correct_option ? 'fw-semibold text-success' : 'text-muted' ?>"><?= Html::encode($optVal) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
