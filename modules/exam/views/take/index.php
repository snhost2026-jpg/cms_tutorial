<?php

use app\modules\exam\models\ExamAttempt;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $exams app\modules\exam\models\Exam[] */
/* @var $attempts app\modules\exam\models\ExamAttempt[] */
/* @var $student app\modules\student\models\StuMaster|null */
/* @var $isAdmin bool */

$this->title = 'My Exams';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="exam-take-index">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-laptop-file text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">
                <?php if ($isAdmin): ?>
                    Showing all published exams (Admin/Staff preview mode).
                <?php else: ?>
                    View your upcoming, active, and completed online exams.
                <?php endif; ?>
            </p>
        </div>
        <?php if ($isAdmin): ?>
            <div>
                <?= Html::a('<i class="fa-solid fa-sliders me-1"></i> Manage Exams', ['/exam/exam/index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        <?php endif; ?>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <?php if (empty($exams)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5 text-muted">
                <i class="fa-solid fa-calendar-check fa-3x mb-3 d-block opacity-50"></i>
                <h5 class="fw-semibold text-dark">No Published Exams Available</h5>
                <p class="small mb-0">When your instructor publishes an exam for your class, it will appear here automatically.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($exams as $exam): ?>
                <?php
                $attempt = $attempts[$exam->id] ?? null;
                $state = $exam->getAvailabilityState();
                $qCount = $exam->getQuestionCount();
                $totalMarks = rtrim(rtrim(number_format($exam->getTotalMarks(), 2), '0'), '.');
                ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h5 class="fw-bold text-dark mb-0"><?= Html::encode($exam->title) ?></h5>
                                <?php if ($attempt && $attempt->status === ExamAttempt::STATUS_SUBMITTED): ?>
                                    <span class="badge bg-success-subtle text-success flex-shrink-0">Completed</span>
                                <?php elseif ($attempt && $attempt->status === ExamAttempt::STATUS_IN_PROGRESS): ?>
                                    <span class="badge bg-warning-subtle text-warning flex-shrink-0">In Progress</span>
                                <?php elseif ($state === 'open'): ?>
                                    <span class="badge bg-primary-subtle text-primary flex-shrink-0">Available</span>
                                <?php elseif ($state === 'upcoming'): ?>
                                    <span class="badge bg-info-subtle text-info flex-shrink-0">Upcoming</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary flex-shrink-0">Ended</span>
                                <?php endif; ?>
                            </div>

                            <div class="small text-muted mb-3">
                                <?php
                                $tags = [];
                                if ($exam->course) $tags[] = $exam->course->name;
                                if ($exam->batch) $tags[] = $exam->batch->name;
                                if ($exam->section) $tags[] = 'Sec ' . $exam->section->name;
                                if ($exam->subject) $tags[] = $exam->subject->name;
                                echo $tags ? Html::encode(implode(' • ', $tags)) : 'General Exam';
                                ?>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3 small">
                                <span class="badge bg-light text-dark border"><i class="fa-solid fa-list-ol me-1 text-primary"></i><?= $qCount ?> Questions</span>
                                <span class="badge bg-light text-dark border"><i class="fa-solid fa-star me-1 text-warning"></i><?= $totalMarks ?> Marks</span>
                                <span class="badge bg-light text-dark border"><i class="fa-regular fa-clock me-1 text-success"></i><?= $exam->duration_minutes ? ($exam->duration_minutes . ' min') : 'Untimed' ?></span>
                            </div>

                            <?php if ($exam->start_time || $exam->end_time): ?>
                                <div class="small text-muted mb-3 bg-light p-2 rounded-3">
                                    <?php if ($exam->start_time): ?>
                                        <div><i class="fa-regular fa-calendar me-1"></i> Starts: <?= Yii::$app->formatter->asDatetime($exam->start_time, 'php:M d, Y h:i A') ?></div>
                                    <?php endif; ?>
                                    <?php if ($exam->end_time): ?>
                                        <div><i class="fa-regular fa-calendar-xmark me-1"></i> Ends: <?= Yii::$app->formatter->asDatetime($exam->end_time, 'php:M d, Y h:i A') ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto pt-2">
                                <?php if ($attempt && $attempt->status === ExamAttempt::STATUS_SUBMITTED): ?>
                                    <?= Html::a('<i class="fa-solid fa-award me-1"></i> View Result', ['result', 'attempt_id' => $attempt->id], ['class' => 'btn btn-outline-success w-100 rounded-pill']) ?>
                                <?php elseif ($attempt && $attempt->status === ExamAttempt::STATUS_IN_PROGRESS): ?>
                                    <?= Html::a('<i class="fa-solid fa-play me-1"></i> Resume Exam', ['paper', 'attempt_id' => $attempt->id], ['class' => 'btn btn-warning w-100 rounded-pill fw-semibold']) ?>
                                <?php elseif ($state === 'open'): ?>
                                    <?= Html::a('<i class="fa-solid fa-arrow-right me-1"></i> Start Exam', ['instructions', 'id' => $exam->id], ['class' => 'btn btn-primary w-100 rounded-pill']) ?>
                                <?php elseif ($state === 'upcoming'): ?>
                                    <button class="btn btn-light border w-100 rounded-pill text-muted" disabled>Not Started Yet</button>
                                <?php else: ?>
                                    <button class="btn btn-light border w-100 rounded-pill text-muted" disabled>Exam Window Closed</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
