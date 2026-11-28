<?php

use app\modules\timetable\models\CoachingTimetable;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var CoachingTimetable $model */
/** @var array $lists */
/** @var bool $canManage */

$this->title = $model->getDayLabel() . ' · ' . $model->getFormattedTimeRange();
$this->params['breadcrumbs'][] = ['label' => 'Weekly Timetable', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Slot #' . $model->id;

$meta = CoachingTimetable::classTypeMeta($model->class_type);
$sessionLabel = $lists['academic_session'][$model->academic_session_id] ?? ('Session #' . $model->academic_session_id);
$courseLabel = $model->course ? $model->course->name : ($lists['course'][$model->course_id] ?? ('Course #' . $model->course_id));
$batchLabel = $model->batch ? $model->batch->name : ($lists['batch'][$model->batch_id] ?? ('Batch #' . $model->batch_id));
$sectionLabel = $model->section
    ? $model->section->name
    : ($model->section_id ? ($lists['section'][$model->section_id] ?? ('Section #' . $model->section_id)) : 'Whole Batch (All Sections)');
$subjectLabel = $model->subject ? $model->subject->name : ($lists['subject'][$model->subject_id] ?? ('Subject #' . $model->subject_id));
$roomLabel = $model->room_id ? ($lists['room'][$model->room_id] ?? ('Room #' . $model->room_id)) : 'Not Assigned';
?>

<div class="timetable-view-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
            <p class="text-muted mb-0"><?= Html::encode($subjectLabel) ?> — <?= Html::encode($batchLabel) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-calendar-days me-1"></i> Weekly View', [
                'index',
                'academic_session_id' => $model->academic_session_id,
                'course_id' => $model->course_id,
                'batch_id' => $model->batch_id,
            ], ['class' => 'btn btn-outline-secondary']) ?>
            <?php if ($canManage): ?>
                <?= Html::a('<i class="fa-solid fa-pen me-1"></i> Update', ['update', 'id' => $model->id], [
                    'class' => 'btn btn-primary',
                ]) ?>
                <?= Html::a(
                    (int) $model->status === CoachingTimetable::STATUS_ACTIVE ? 'Deactivate' : 'Activate',
                    ['toggle-status', 'id' => $model->id],
                    [
                        'class' => 'btn btn-outline-warning',
                        'data-method' => 'post',
                    ]
                ) ?>
                <?= Html::a('<i class="fa-solid fa-trash-can me-1"></i> Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-outline-danger',
                    'data-confirm' => 'Are you sure you want to delete this timetable slot?',
                    'data-method' => 'post',
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="badge border <?= $meta['badge'] ?> px-3 py-2 fs-6">
                    <i class="fa-solid <?= $meta['icon'] ?> me-1"></i><?= Html::encode($model->class_type) ?>
                </span>
                <?php if ((int) $model->status === CoachingTimetable::STATUS_ACTIVE): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Active</span>
                <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border px-3 py-2">Inactive</span>
                <?php endif; ?>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small text-uppercase">Academic Session</div>
                        <div class="fw-bold mt-1"><?= Html::encode($sessionLabel) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small text-uppercase">Course</div>
                        <div class="fw-bold mt-1"><?= Html::encode($courseLabel) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small text-uppercase">Batch &amp; Section</div>
                        <div class="fw-bold mt-1"><?= Html::encode($batchLabel) ?> · <?= Html::encode($sectionLabel) ?></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small text-uppercase">Subject</div>
                        <div class="fw-bold mt-1"><?= Html::encode($subjectLabel) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small text-uppercase">Teacher / Faculty</div>
                        <div class="fw-bold mt-1"><?= Html::encode($model->getTeacherDisplayName()) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small text-uppercase">Room</div>
                        <div class="fw-bold mt-1"><?= Html::encode($roomLabel) ?></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small text-uppercase">Day &amp; Time</div>
                        <div class="fw-bold mt-1">
                            <?= Html::encode($model->getDayLabel()) ?> (<?= Html::encode($model->getFormattedTimeRange()) ?>)
                        </div>
                        <div class="text-muted small"><?= $model->getDurationMinutes() ?> minutes</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small text-uppercase">Effective Period</div>
                        <div class="fw-bold mt-1">
                            <?= Html::encode($model->effective_from) ?> → <?= Html::encode($model->effective_to ?: 'Ongoing') ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small text-uppercase">Audit</div>
                        <div class="small mt-1 text-muted">
                            Created: <?= Html::encode($model->created_at ?: '-') ?><br>
                            Updated: <?= Html::encode($model->updated_at ?: '-') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
