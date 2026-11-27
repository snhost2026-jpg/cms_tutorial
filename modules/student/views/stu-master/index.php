<?php

use yii\grid\GridView;
use yii\helpers\Html;
use app\modules\student\models\StuInfo;
use app\modules\student\models\StuMaster;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\student\models\StuMasterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $lists array */

$this->title = 'Student Management';
$this->params['breadcrumbs'][] = $this->title;

$label = function ($key, $id) use ($lists) {
    if ($id === null || $id === '') {
        return '';
    }
    return isset($lists[$key][$id]) ? $lists[$key][$id] : $id;
};
$filterFor = function ($key) use ($lists) {
    return !empty($lists[$key]) ? $lists[$key] : null;
};
?>
<div class="student-index">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><?= Html::encode($this->title) ?></h3>
            <p class="text-muted small mb-0">Browse student directory, admissions, and class placements.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-file-csv me-1"></i> Export CSV', array_merge(['export'], Yii::$app->request->queryParams), [
                'class' => 'btn btn-outline-secondary',
                'data-pjax' => 0,
            ]) ?>
            <?= Html::a('<i class="fa-solid fa-trash-can me-1"></i> Trash', ['trash'], [
                'class' => 'btn btn-outline-danger',
            ]) ?>
            <?= Html::a('<i class="fa-solid fa-user-plus me-1"></i> Add Student', ['create'], [
                'class' => 'btn btn-primary',
            ]) ?>
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
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                    'summaryOptions' => ['class' => 'p-3 text-muted small border-bottom mb-0'],
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'headerOptions' => ['style' => 'width: 60px;', 'class' => 'text-muted ps-3'],
                            'contentOptions' => ['class' => 'text-muted ps-3'],
                        ],
                        [
                            'attribute' => 'stu_unique_id',
                            'label' => 'Reg. No',
                            'format' => 'raw',
                            'value' => function ($m) {
                                if (!$m->info || empty($m->info->stu_unique_id)) return '<span class="text-muted">-</span>';
                                return '<span class="badge bg-light text-dark border px-2 py-1 font-monospace">' . Html::encode($m->info->stu_unique_id) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'name',
                            'label' => 'Name',
                            'format' => 'raw',
                            'value' => function ($m) {
                                $name = $m->info ? $m->info->fullName : '(no info)';
                                return Html::a('<i class="fa-solid fa-user text-primary me-1"></i>' . Html::encode($name), ['view', 'id' => $m->stu_master_id], [
                                    'class' => 'fw-semibold text-decoration-none text-dark',
                                ]);
                            },
                        ],
                        [
                            'attribute' => 'gender',
                            'label' => 'Gender',
                            'filter' => StuInfo::genderOptions(),
                            'format' => 'raw',
                            'value' => function ($m) {
                                $g = $m->info ? $m->info->stu_gender : null;
                                if (!$g) return '<span class="text-muted">-</span>';
                                return Html::encode($g);
                            },
                        ],
                        [
                            'attribute' => 'stu_master_course_id',
                            'label' => 'Course',
                            'filter' => $filterFor('course'),
                            'value' => function ($m) use ($label) { return $label('course', $m->stu_master_course_id) ?: '-'; },
                        ],
                        [
                            'attribute' => 'stu_master_batch_id',
                            'label' => 'Batch',
                            'filter' => $filterFor('batch'),
                            'value' => function ($m) use ($label) { return $label('batch', $m->stu_master_batch_id) ?: '-'; },
                        ],
                        [
                            'attribute' => 'stu_master_section_id',
                            'label' => 'Section',
                            'filter' => $filterFor('section'),
                            'value' => function ($m) use ($label) { return $label('section', $m->stu_master_section_id) ?: '-'; },
                        ],
                        [
                            'attribute' => 'mobile',
                            'label' => 'Mobile',
                            'value' => function ($m) { return ($m->info && $m->info->stu_mobile_no) ? $m->info->stu_mobile_no : '-'; },
                        ],
                        [
                            'label' => 'Father',
                            'value' => function ($m) { return ($m->info && $m->info->father_name) ? $m->info->father_name : '-'; },
                        ],
                        [
                            'attribute' => 'is_status',
                            'label' => 'Status',
                            'filter' => StuMaster::statusOptions(),
                            'format' => 'raw',
                            'value' => function ($m) {
                                if ($m->is_status == StuMaster::STATUS_ACTIVE) {
                                    return '<span class="badge bg-success-subtle text-success px-2 py-1">Active</span>';
                                } elseif ($m->is_status == StuMaster::STATUS_INACTIVE) {
                                    return '<span class="badge bg-secondary-subtle text-secondary px-2 py-1">Inactive</span>';
                                }
                                return Html::encode($m->statusLabel);
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'Actions',
                            'headerOptions' => ['class' => 'text-end pe-3'],
                            'contentOptions' => ['class' => 'text-end pe-3 text-nowrap'],
                            'buttons' => [
                                'view' => function ($url) {
                                    return Html::a('<i class="fa-solid fa-eye"></i>', $url, [
                                        'class' => 'btn btn-sm btn-outline-secondary me-1',
                                        'title' => 'View',
                                    ]);
                                },
                                'update' => function ($url) {
                                    return Html::a('<i class="fa-solid fa-pen-to-square"></i>', $url, [
                                        'class' => 'btn btn-sm btn-outline-primary me-1',
                                        'title' => 'Edit',
                                    ]);
                                },
                                'delete' => function ($url) {
                                    return Html::a('<i class="fa-solid fa-trash"></i>', $url, [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'title' => 'Delete',
                                        'data' => [
                                            'confirm' => 'Move this student to the trash?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                },
                            ],
                            'urlCreator' => function ($action, $model) {
                                return [$action, 'id' => $model->stu_master_id];
                            },
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>
