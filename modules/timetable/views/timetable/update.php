<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\timetable\models\CoachingTimetable $model */
/** @var array $lists */
/** @var array $placementMeta */

$this->title = 'Update Timetable Slot #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Weekly Timetable', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Slot #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="timetable-update-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
            <p class="text-muted mb-0">
                Modify day, time, teacher, room, or validity dates for this scheduled period.
            </p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-calendar-days me-1"></i> Weekly View', [
                'index',
                'academic_session_id' => $model->academic_session_id,
                'course_id' => $model->course_id,
                'batch_id' => $model->batch_id,
            ], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'lists' => $lists,
        'placementMeta' => $placementMeta,
    ]) ?>
</div>
