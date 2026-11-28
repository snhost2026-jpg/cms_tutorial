<?php

use app\modules\employee\models\Employee;
use app\modules\employee\models\EmployeeInfo;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $filters */

$this->title = 'Employee Management';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
        <p class="text-muted mb-0">Manage teaching and non-teaching employees.</p>
    </div>
    <?= Html::a('Add Employee', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<?php foreach (['success', 'error'] as $flash): ?>
    <?php if (Yii::$app->session->hasFlash($flash)): ?>
        <div class="alert alert-<?= $flash === 'success' ? 'success' : 'danger' ?>">
            <?= Html::encode(Yii::$app->session->getFlash($flash)) ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body">
        <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <div class="col-md-5">
                <?= Html::label('Search', 'employee-search', ['class' => 'form-label']) ?>
                <?= Html::textInput('q', $filters['q'], [
                    'id' => 'employee-search',
                    'class' => 'form-control',
                    'placeholder' => 'Employee ID, code, name, or contact',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= Html::label('Employee Type', 'employee-type', ['class' => 'form-label']) ?>
                <?= Html::dropDownList('employee_type', $filters['employee_type'], EmployeeInfo::getTypeOptions(), [
                    'id' => 'employee-type',
                    'class' => 'form-select',
                    'prompt' => 'All types',
                ]) ?>
            </div>
            <div class="col-md-2">
                <?= Html::label('Status', 'employee-status', ['class' => 'form-label']) ?>
                <?= Html::dropDownList('status', $filters['status'], Employee::getStatusOptions(), [
                    'id' => 'employee-status',
                    'class' => 'form-select',
                    'prompt' => 'All statuses',
                ]) ?>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <?= Html::submitButton('Filter', ['class' => 'btn btn-outline-primary']) ?>
                <?= Html::a('Reset', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover align-middle'],
            'columns' => [
                'emp_master_id',
                'info.employee_code',
                [
                    'label' => 'Full Name',
                    'value' => function (Employee $model) {
                        return $model->info->fullName ?? '-';
                    },
                ],
                [
                    'label' => 'Employee Type',
                    'value' => function (Employee $model) {
                        $info = $model->info;
                        return $info === null ? '-' : (EmployeeInfo::getTypeOptions()[$info->employee_type] ?? '-');
                    },
                ],
                'info.designation',
                'info.department',
                [
                    'label' => 'Email',
                    'format' => 'email',
                    'value' => function (Employee $model) {
                        return $model->info->emp_email_id ?? null;
                    },
                ],
                'info.emp_mobile_no',
                [
                    'attribute' => 'is_status',
                    'value' => function (Employee $model) {
                        return Employee::getStatusOptions()[$model->is_status] ?? '-';
                    },
                ],
                [
                    'class' => \yii\grid\ActionColumn::class,
                    'template' => '{view} {update} {delete}',
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'buttons' => [
                        'delete' => function ($url, $model) {
                            return Html::a('Delete', $url, [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => 'Delete this employee?',
                                ],
                            ]);
                        },
                    ],
                ],
            ],
        ]) ?>
    </div>
</div>
