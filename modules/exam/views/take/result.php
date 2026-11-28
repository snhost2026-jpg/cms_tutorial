<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $attempt app\modules\exam\models\ExamAttempt */
/* @var $exam app\modules\exam\models\Exam */
/* @var $showResult bool */
/* @var $questionIds int[] */
/* @var $questions app\modules\exam\models\ExamQuestion[] */
/* @var $answers app\modules\exam\models\ExamAttemptAnswer[] */

$this->title = 'Result: ' . $exam->title;
$this->params['breadcrumbs'][] = ['label' => 'My Exams', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Result';
?>
<div class="exam-take-result">
    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <?php if (!$showResult): ?>
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 text-center p-5">
                    <div class="mb-3"><i class="fa-solid fa-circle-check text-success fa-4x"></i></div>
                    <h3 class="fw-bold text-dark mb-2">Exam Submitted Successfully!</h3>
                    <p class="text-muted mb-4">
                        Your responses for <strong><?= Html::encode($exam->title) ?></strong> have been recorded.
                        Results will be shared by your instructor.
                    </p>
                    <div>
                        <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to My Exams', ['index'], ['class' => 'btn btn-primary px-4 rounded-pill']) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Score Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="fw-bold text-dark mb-1"><?= Html::encode($exam->title) ?></h3>
                        <p class="text-muted small mb-0">Submitted on <?= Yii::$app->formatter->asDatetime($attempt->submitted_at) ?> &bull; Time taken: <?= Html::encode($attempt->getFormattedDuration()) ?></p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($attempt->is_passed === 1): ?>
                            <span class="badge bg-success fs-6 px-3 py-2 rounded-pill"><i class="fa-solid fa-trophy me-1"></i> PASSED</span>
                        <?php elseif ($attempt->is_passed === 0): ?>
                            <span class="badge bg-danger fs-6 px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-xmark me-1"></i> NOT PASSED</span>
                        <?php else: ?>
                            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill"><i class="fa-solid fa-check me-1"></i> COMPLETED</span>
                        <?php endif; ?>
                        <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> My Exams', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>

                <div class="row g-3 text-center">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Score</div>
                            <div class="fs-4 fw-bold text-primary">
                                <?= rtrim(rtrim(number_format((float) $attempt->obtained_marks, 2), '0'), '.') ?> / <?= rtrim(rtrim(number_format((float) $attempt->total_marks, 2), '0'), '.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Percentage</div>
                            <div class="fs-4 fw-bold text-dark"><?= number_format((float) $attempt->percentage, 1) ?>%</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Correct Answers</div>
                            <div class="fs-4 fw-bold text-success"><?= $attempt->correct_count ?> / <?= $attempt->total_questions ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Answered</div>
                            <div class="fs-4 fw-bold text-dark"><?= $attempt->answered_count ?> / <?= $attempt->total_questions ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Answer Review -->
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i>Answer Review</h5>
        <div class="d-flex flex-column gap-3">
            <?php $n = 1; foreach ($questionIds as $qid): ?>
                <?php if (!isset($questions[$qid])) continue; $q = $questions[$qid]; $ans = $answers[$qid] ?? null; ?>
                <?php
                $selected = $ans ? $ans->selected_option : null;
                $isCorrect = $selected && strtoupper($selected) === strtoupper($q->correct_option);
                $borderClass = !$selected ? 'border-secondary' : ($isCorrect ? 'border-success' : 'border-danger');
                ?>
                <div class="card border-0 shadow-sm rounded-4 border-start border-4 <?= $borderClass ?>">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div class="fw-semibold text-dark">
                                <span class="badge bg-light text-dark border me-2">Q<?= $n++ ?></span>
                                <?= Html::encode($q->question_text) ?>
                            </div>
                            <div class="flex-shrink-0">
                                <?php if (!$selected): ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Unanswered</span>
                                <?php elseif ($isCorrect): ?>
                                    <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i>Correct</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-xmark me-1"></i>Incorrect</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row g-2">
                            <?php foreach ($q->getAvailableOptions() as $key => $val): ?>
                                <?php
                                $cls = 'bg-light text-dark';
                                if ($key === $q->correct_option) {
                                    $cls = 'bg-success-subtle text-success border border-success';
                                } elseif ($key === $selected && !$isCorrect) {
                                    $cls = 'bg-danger-subtle text-danger border border-danger';
                                }
                                ?>
                                <div class="col-md-6">
                                    <div class="p-2 px-3 rounded-3 small <?= $cls ?>">
                                        <strong><?= $key ?>.</strong> <?= Html::encode($val) ?>
                                        <?php if ($key === $selected): ?><span class="badge bg-dark ms-1">Your Answer</span><?php endif; ?>
                                        <?php if ($key === $q->correct_option): ?><span class="badge bg-success ms-1">Correct</span><?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
