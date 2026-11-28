<?php

use app\modules\timetable\models\CoachingTimetable;
use app\modules\timetable\models\CoachingTimetableSearch;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var CoachingTimetableSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $lists */
/** @var bool $canManage */

$this->title = 'All Timetable Entries';
$this->params['breadcrumbs'][] = ['label' => 'Weekly Timetable', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="timetable-list-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
            <p class="text-muted mb-0">Search, filter, activate/deactivate, or edit individual coaching timetable records.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-calendar-days me-1"></i> Weekly Grid View', ['index'], ['class' => 'btn btn-outline-primary']) ?>
            <?php if ($canManage): ?>
                <?= Html::a('<i class="fa-solid fa-calendar-week me-1"></i> Weekly Builder', ['builder'], ['class' => 'btn btn-outline-secondary']) ?>
                <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Add Slot', ['create'], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'summaryOptions' => ['class' => 'p-3 text-muted small border-bottom mb-0'],
                'columns' => [
                    [
                        'attribute' => 'day_of_week',
                        'label' => 'Day',
                        'filter' => CoachingTimetable::dayOptions(),
                        'format' => 'raw',
                        'value' => static function (CoachingTimetable $m): string {
                            return '<span class="fw-semibold">' . Html::encode($m->getDayLabel()) . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'start_time',
                        'label' => 'Timing',
                        'filter' => false,
                        'value' => static function (CoachingTimetable $m): string {
                            return $m->getFormattedTimeRange();
                        },
                    ],
                    [
                        'attribute' => 'course_id',
                        'label' => 'Course / Batch',
                        'filter' => !empty($lists['course']) ? $lists['course'] : null,
                        'format' => 'raw',
                        'value' => static function (CoachingTimetable $m) use ($lists): string {
                            $course = $m->course ? $m->course->name : ($lists['course'][$m->course_id] ?? ('Course #' . $m->course_id));
                            $batch = $m->batch ? $m->batch->name : ($lists['batch'][$m->batch_id] ?? ('Batch #' . $m->batch_id));
                            $sec = $m->section ? (' · ' . $m->section->name) : '';
                            return '<div class="fw-semibold">' . Html::encode($batch . $sec) . '</div>'
                                . '<div class="text-muted small">' . Html::encode($course) . '</div>';
                        },
                    ],
                    [
                        'attribute' => 'subject_id',
                        'label' => 'Subject',
                        'filter' => !empty($lists['subject']) ? $lists['subject'] : null,
                        'value' => static function (CoachingTimetable $m) use ($lists): string {
                            return $m->subject ? $m->subject->name : ($lists['subject'][$m->subject_id] ?? ('Subject #' . $m->subject_id));
                        },
                    ],
                    [
                        'attribute' => 'employee_id',
                        'label' => 'Teacher',
                        'filter' => !empty($lists['employee']) ? $lists['employee'] : null,
                        'value' => static function (CoachingTimetable $m): string {
                            return $m->getTeacherDisplayName();
                        },
                    ],
                    [
                        'attribute' => 'class_type',
                        'label' => 'Type',
                        'filter' => CoachingTimetable::classTypeOptions(),
                        'format' => 'raw',
                        'value' => static function (CoachingTimetable $m): string {
                            $meta = CoachingTimetable::classTypeMeta($m->class_type);
                            return '<span class="badge border ' . $meta['badge'] . '">' . Html::encode($m->class_type) . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'effective_from',
                        'label' => 'Effective Period',
                        'filter' => false,
                        'format' => 'raw',
                        'value' => static function (CoachingTimetable $m): string {
                            $to = $m->effective_to ?: 'Ongoing';
                            return '<span class="small text-muted">' . Html::encode($m->effective_from) . ' → ' . Html::encode($to) . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'filter' => CoachingTimetable::statusOptions(),
                        'format' => 'raw',
                        'value' => static function (CoachingTimetable $m): string {
                            return (int) $m->status === CoachingTimetable::STATUS_ACTIVE
                                ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>'
                                : '<span class="badge bg-secondary-subtle text-secondary border">Inactive</span>';
                        },
                    ],
                    [
                        'header' => 'Actions',
                        'headerOptions' => ['class' => 'text-end pe-3'],
                        'contentOptions' => ['class' => 'text-end pe-3 text-nowrap'],
                        'format' => 'raw',
                        'value' => static function (CoachingTimetable $m) use ($canManage): string {
                            $btns = [
                                Html::a('<i class="fa-regular fa-eye"></i>', ['view', 'id' => $m->id], [
                                    'class' => 'btn btn-sm btn-outline-secondary',
                                    'title' => 'View',
                                ]),
                            ];
                            if ($canManage) {
                                $btns[] = Html::a('<i class="fa-solid fa-pen"></i>', ['update', 'id' => $m->id], [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'title' => 'Update',
                                ]);
                                $btns[] = Html::a(
                                    (int) $m->status === CoachingTimetable::STATUS_ACTIVE
                                        ? '<i class="fa-solid fa-pause"></i>'
                                        : '<i class="fa-solid fa-play"></i>',
                                    ['toggle-status', 'id' => $m->id],
                                    [
                                        'class' => 'btn btn-sm btn-outline-warning',
                                        'title' => (int) $m->status === CoachingTimetable::STATUS_ACTIVE ? 'Deactivate' : 'Activate',
                                        'data-method' => 'post',
                                    ]
                                );
                                $btns[] = Html::a('<i class="fa-solid fa-trash-can"></i>', ['delete', 'id' => $m->id], [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => 'Delete',
                                    'data-confirm' => 'Delete this timetable slot?',
                                    'data-method' => 'post',
                                ]);
                            }
                            return implode(' ', $btns);
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
