<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\timetable\models\CoachingTimetable $model */
/** @var array $lists */
/** @var array $placementMeta */

$this->title = 'Add Timetable Slot';
$this->params['breadcrumbs'][] = ['label' => 'Weekly Timetable', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="timetable-create-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
            <p class="text-muted mb-0">Schedule a new class period for one or multiple days of the week.</p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-calendar-week me-1"></i> Weekly Builder', ['builder'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Weekly View', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'lists' => $lists,
        'placementMeta' => $placementMeta,
    ]) ?>
</div>
