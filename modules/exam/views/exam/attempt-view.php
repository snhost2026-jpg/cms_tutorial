<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $attempt app\modules\exam\models\ExamAttempt */
/* @var $exam app\modules\exam\models\Exam */
/* @var $questionIds int[] */
/* @var $questions app\modules\exam\models\ExamQuestion[] */
/* @var $answers app\modules\exam\models\ExamAttemptAnswer[] */

$this->title = 'Answer Sheet: ' . $attempt->getStudentDisplayName();
$this->params['breadcrumbs'][] = ['label' => 'Online Exams', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $exam->title, 'url' => ['results', 'id' => $exam->id]];
$this->params['breadcrumbs'][] = $attempt->getStudentDisplayName();
?>
<div class="exam-attempt-view">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-file-lines text-primary me-2"></i><?= Html::encode($attempt->getStudentDisplayName()) ?>
            </h3>
            <p class="text-muted small mb-0">
                <?= Html::encode($exam->title) ?> &bull; Score: <strong><?= rtrim(rtrim(number_format((float) $attempt->obtained_marks, 2), '0'), '.') ?> / <?= rtrim(rtrim(number_format((float) $attempt->total_marks, 2), '0'), '.') ?></strong> (<?= number_format((float) $attempt->percentage, 1) ?>%)
            </p>
        </div>
        <div>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Results', ['results', 'id' => $exam->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

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
                                <span class="badge bg-secondary-subtle text-secondary">Unanswered (0/<?= rtrim(rtrim(number_format((float) $q->marks, 2), '0'), '.') ?>)</span>
                            <?php elseif ($isCorrect): ?>
                                <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i>Correct (+<?= rtrim(rtrim(number_format((float) $q->marks, 2), '0'), '.') ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-xmark me-1"></i>Incorrect (0/<?= rtrim(rtrim(number_format((float) $q->marks, 2), '0'), '.') ?>)</span>
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
                                    <?php if ($key === $selected): ?><span class="badge bg-dark ms-1">Selected</span><?php endif; ?>
                                    <?php if ($key === $q->correct_option): ?><span class="badge bg-success ms-1">Correct</span><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
