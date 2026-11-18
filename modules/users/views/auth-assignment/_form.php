<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\users\models\AuthAssignment */
/* @var $form yii\widgets\ActiveForm */
/* @var $itemsList array */
/* @var $usersList array */

$isCreate = $model->isNewRecord;
?>

<div class="auth-assignment-form">

    <?php $form = ActiveForm::begin(['id' => 'auth-assignment-form']); ?>

    <div class="row g-3">
        <div class="col-md-5">
            <?php if (!empty($usersList)): ?>
                <?= $form->field($model, 'user_id')->dropDownList($usersList, [
                    'prompt' => '-- Select User --',
                    'class' => 'form-select',
                    'disabled' => !$isCreate,
                ]) ?>
            <?php else: ?>
                <?= $form->field($model, 'user_id')->textInput(['maxlength' => 64, 'class' => 'form-control', 'placeholder' => 'Enter user ID']) ?>
            <?php endif; ?>

            <div class="card bg-light border-0 p-3 rounded-3 mt-3">
                <h6 class="fw-bold mb-2 small text-uppercase text-muted"><i class="fa-solid fa-circle-info me-1 text-primary"></i> CMS Access Rules:</h6>
                <ul class="small text-muted mb-0 ps-3" style="line-height: 1.6;">
                    <li><strong>Roles:</strong> Assign complete pre-defined roles (e.g. <code>SuperAdmin</code>, <code>Employee</code>, <code>bde</code>).</li>
                    <li><strong>Page Routes:</strong> Grant access to specific pages or entire controller sections (e.g. <code>/sales/*</code>, <code>/sales/lead/index</code>).</li>
                    <li><strong>Multi-Select:</strong> Hold <kbd>Ctrl</kbd> (or <kbd>Cmd</kbd>) to select multiple items at once.</li>
                </ul>
            </div>
        </div>

        <div class="col-md-7">
            <?php if ($isCreate): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Roles, Permissions & Page Routes <span class="text-danger">*</span></label>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">Hold Ctrl to select multiple:</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="filterItems('')">All</button>
                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="filterItems('Page Access: Sales')">Sales</button>
                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="filterItems('Page Access: Financial')">Finance</button>
                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="filterItems('System Roles')">Roles</button>
                        </div>
                    </div>
                    <?= Html::dropDownList('AuthAssignment[item_names][]', [], $itemsList, [
                        'id' => 'auth-item-selector',
                        'multiple' => true,
                        'class' => 'form-select font-monospace small',
                        'size' => 16,
                        'style' => 'border-radius: 10px;',
                    ]) ?>
                </div>
            <?php else: ?>
                <div class="mb-3">
                    <?= $form->field($model, 'item_name')->dropDownList($itemsList, [
                        'prompt' => '-- Select Role, Permission or Page Route --',
                        'class' => 'form-select font-monospace small',
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group mt-4 d-flex gap-2">
        <?= Html::submitButton($isCreate ? '<i class="fa-solid fa-check me-1"></i> Save Selected Assignments' : '<i class="fa-solid fa-floppy-disk me-1"></i> Update Assignment', [
            'class' => $isCreate ? 'btn btn-success px-4 fw-semibold' : 'btn btn-primary px-4 fw-semibold'
        ]) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-outline-secondary px-3']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
function filterItems(keyword) {
    var select = document.getElementById('auth-item-selector');
    if (!select) return;
    var optgroups = select.getElementsByTagName('optgroup');
    for (var i = 0; i < optgroups.length; i++) {
        var og = optgroups[i];
        if (!keyword || og.label.toLowerCase().includes(keyword.toLowerCase())) {
            og.style.display = '';
        } else {
            og.style.display = 'none';
        }
    }
}
</script>
