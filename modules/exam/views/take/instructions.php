<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $exam app\modules\exam\models\Exam */
/* @var $attempt app\modules\exam\models\ExamAttempt|null */
/* @var $questionCount int */
/* @var $totalMarks float */

$this->title = 'Instructions: ' . $exam->title;
$this->params['breadcrumbs'][] = ['label' => 'My Exams', 'url' => ['index']];
$this->params['breadcrumbs'][] = $exam->title;

$isOpen = $exam->isOpenNow();
?>
<div class="exam-take-instructions">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-4 px-4">
                    <h4 class="fw-bold mb-1"><?= Html::encode($exam->title) ?></h4>
                    <div class="small opacity-75">
                        <?php
                        $parts = [];
                        if ($exam->course) $parts[] = $exam->course->name;
                        if ($exam->batch) $parts[] = $exam->batch->name;
                        if ($exam->section) $parts[] = 'Section ' . $exam->section->name;
                        if ($exam->subject) $parts[] = $exam->subject->name;
                        echo $parts ? Html::encode(implode(' • ', $parts)) : 'Online Examination';
                        ?>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
                        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?>"><?= Html::encode($message) ?></div>
                    <?php endforeach; ?>

                    <div class="row g-3 mb-4 text-center">
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">Questions</div>
                                <div class="fs-5 fw-bold text-dark"><?= $questionCount ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">Total Marks</div>
                                <div class="fs-5 fw-bold text-dark"><?= rtrim(rtrim(number_format($totalMarks, 2), '0'), '.') ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">Duration</div>
                                <div class="fs-5 fw-bold text-dark"><?= $exam->duration_minutes ? ($exam->duration_minutes . ' min') : 'Untimed' ?></div>
                            </div>
                        </div>
                    </div>

                    <?php if ($exam->description): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark">Exam Notes</h6>
                            <p class="text-muted mb-0"><?= nl2br(Html::encode($exam->description)) ?></p>
                        </div>
                    <?php endif; ?>

                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-circle-info text-primary me-1"></i> General Instructions</h6>
                    <ul class="text-muted small mb-4">
                        <li>Your answers are saved automatically as soon as you select an option.</li>
                        <li>You can navigate freely between questions using the Question Palette or Next/Previous buttons.</li>
                        <li>Use <strong>Mark for Review</strong> to flag any question you want to revisit before submitting.</li>
                        <?php if ($exam->duration_minutes): ?>
                            <li>Once started, the timer runs continuously for <strong><?= $exam->duration_minutes ?> minutes</strong> and will auto-submit when time expires.</li>
                        <?php endif; ?>
                        <?php if ($exam->pass_percentage !== null): ?>
                            <li>Minimum passing score is <strong><?= rtrim(rtrim(number_format((float) $exam->pass_percentage, 2), '0'), '.') ?>%</strong>.</li>
                        <?php endif; ?>
                        <li>You can only submit this exam once.</li>
                    </ul>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to My Exams', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                        <?php if ($attempt): ?>
                            <?= Html::a('<i class="fa-solid fa-play me-1"></i> Resume Exam', ['paper', 'attempt_id' => $attempt->id], ['class' => 'btn btn-warning px-4 fw-semibold']) ?>
                        <?php elseif ($isOpen && $questionCount > 0): ?>
                            <?= Html::beginForm(['start', 'id' => $exam->id], 'post', ['class' => 'd-inline']) ?>
                                <?= Html::submitButton('<i class="fa-solid fa-play me-1"></i> Begin Exam Now', ['class' => 'btn btn-primary px-4 fw-semibold']) ?>
                            <?= Html::endForm() ?>
                        <?php else: ?>
                            <button class="btn btn-secondary px-4" disabled>Not Available</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
