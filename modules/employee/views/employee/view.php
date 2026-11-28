<?php

use app\modules\employee\models\Employee;
use app\modules\employee\models\EmployeeInfo;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var Employee $model */

$info = $model->info;
$this->title = $info === null ? 'Employee' : $info->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Employee Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$infoAttributes = array_map(function ($attribute) {
    if ($attribute === 'employee_type') {
        return [
            'attribute' => 'employee_type',
            'value' => function (EmployeeInfo $model) {
                return EmployeeInfo::getTypeOptions()[$model->employee_type] ?? '-';
            },
        ];
    }

    return $attribute;
}, EmployeeInfo::dataAttributes());
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0"><?= Html::encode($this->title) ?></h2>
    <div class="d-flex gap-2">
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('Update', ['update', 'id' => $model->emp_master_id], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'emp_master_id',
                [
                    'attribute' => 'is_status',
                    'value' => Employee::getStatusOptions()[$model->is_status] ?? '-',
                ],
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
    </div>
</div>

<?php if ($info !== null): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $info,
                'attributes' => $infoAttributes,
            ]) ?>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-warning">Employee information has not been added yet.</div>
<?php endif; ?>
