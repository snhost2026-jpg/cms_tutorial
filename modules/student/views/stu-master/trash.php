<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\student\models\StuMasterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Deleted Students (Trash)';
$this->params['breadcrumbs'][] = ['label' => 'Students', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="student-trash">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-trash-can text-danger me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Review archived or soft-deleted students. You can restore them to active status at any time.</p>
        </div>
        <div>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Students', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'headerRowOptions' => ['class' => 'table-light text-uppercase fs-7 text-muted border-bottom'],
                'layout' => "{items}\n<div class='p-3 border-top d-flex justify-content-between align-items-center'>{summary}{pager}</div>",
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width: 60px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; color: #6c757d;'],
                    ],
                    [
                        'attribute' => 'stu_unique_id',
                        'label' => 'Reg. No',
                        'format' => 'raw',
                        'value' => function ($m) {
                            $val = $m->info ? $m->info->stu_unique_id : null;
                            return $val ? '<span class="badge bg-light text-dark border font-monospace">' . Html::encode($val) . '</span>' : '<span class="text-muted">—</span>';
                        },
                    ],
                    [
                        'attribute' => 'name',
                        'label' => 'Student Name',
                        'format' => 'raw',
                        'value' => function ($m) {
                            $name = $m->info ? $m->info->fullName : '(no info)';
                            return '<div class="fw-semibold text-dark"><i class="fa-solid fa-user-xmark text-muted me-2"></i>' . Html::encode($name) . '</div>';
                        },
                    ],
                    [
                        'attribute' => 'updated_at',
                        'label' => 'Deleted At',
                        'format' => 'datetime',
                    ],
                    [
                        'label' => 'Actions',
                        'format' => 'raw',
                        'headerOptions' => ['class' => 'text-end px-3', 'style' => 'width: 140px;'],
                        'contentOptions' => ['class' => 'text-end px-3'],
                        'value' => function ($m) {
                            return Html::a('<i class="fa-solid fa-rotate-left me-1"></i> Restore', ['restore', 'id' => $m->stu_master_id], [
                                'class' => 'btn btn-sm btn-outline-success',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => 'Are you sure you want to restore this student to active status?',
                                ],
                            ]);
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
