<?php

use mdm\admin\AnimateAsset;
use mdm\admin\components\ItemController;
use mdm\admin\models\AuthItem;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;
use yii\web\YiiAsset;

/* @var $this View */
/* @var $model AuthItem */
/* @var $context ItemController */

$context = $this->context;
$labels = $context->labels();
$isRole = ($labels['Item'] === 'Role');

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Users & Access', 'url' => ['/users/user/index']];
$this->params['breadcrumbs'][] = ['label' => $labels['Items'], 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

AnimateAsset::register($this);
YiiAsset::register($this);

$opts = Json::htmlEncode([
    'items' => $model->getItems(),
    'users' => $model->getUsers(),
    'getUserUrl' => Url::to(['get-users', 'id' => $model->name])
]);
$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
?>

<div class="auth-item-view py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="rounded-circle <?= $isRole ? 'bg-primary text-white' : 'bg-info text-white' ?> fw-bold d-inline-flex align-items-center justify-content-center shadow-sm" style="width:38px;height:38px;font-size:16px;">
                    <i class="fa-solid <?= $isRole ? 'fa-shield-halved' : 'fa-key' ?>"></i>
                </span>
                <h3 class="fw-bold mb-0 text-dark"><?= Html::encode($model->name) ?></h3>
                <span class="badge bg-light text-muted border ms-1"><?= Html::encode($labels['Item']) ?></span>
            </div>
            <p class="text-muted small mb-0"><?= Html::encode($model->description ?: 'No description provided') ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to ' . $labels['Items'], ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-pen me-1"></i> Edit Details', ['update', 'id' => $model->name], ['class' => 'btn btn-outline-dark rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-plus me-1"></i> New ' . $labels['Item'], ['create'], ['class' => 'btn btn-primary rounded-3 shadow-sm']) ?>
            <?= Html::a('<i class="fa-solid fa-trash me-1"></i> Delete', ['delete', 'id' => $model->name], [
                'class' => 'btn btn-outline-danger rounded-3',
                'data-confirm' => 'Are you sure you want to delete this ' . strtolower($labels['Item']) . '?',
                'data-method' => 'post',
            ]) ?>
        </div>
    </div>

    <!-- Assigned Users Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-users text-primary me-2"></i>Users with this <?= Html::encode($labels['Item']) ?>
            </h6>
        </div>
        <div class="card-body p-3">
            <div id="list-users" class="d-flex gap-2 flex-wrap align-items-center">
                <span class="text-muted small">Loading users...</span>
            </div>
        </div>
    </div>

    <!-- Dual Listbox Card for permissions / routes -->
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="row g-4 align-items-center">
            <!-- Available Column -->
            <div class="col-lg-5">
                <div class="card border rounded-4 bg-light bg-opacity-50 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-boxes-stacked text-primary me-1"></i> Available Permissions &amp; Routes
                        </label>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1" id="available-count">0 items</span>
                    </div>

                    <!-- Filter pills -->
                    <div class="btn-group btn-group-sm w-100 mb-2 shadow-sm" role="group">
                        <input type="radio" class="btn-check filter-type" name="filter-available" id="av-all" value="" checked data-target="available">
                        <label class="btn btn-outline-secondary" for="av-all">All</label>

                        <?php if ($isRole): ?>
                            <input type="radio" class="btn-check filter-type" name="filter-available" id="av-role" value="role" data-target="available">
                            <label class="btn btn-outline-secondary" for="av-role">Roles</label>
                        <?php endif; ?>

                        <input type="radio" class="btn-check filter-type" name="filter-available" id="av-perm" value="permission" data-target="available">
                        <label class="btn btn-outline-secondary" for="av-perm">Permissions</label>

                        <input type="radio" class="btn-check filter-type" name="filter-available" id="av-route" value="route" data-target="available">
                        <label class="btn btn-outline-secondary" for="av-route">Routes (/)</label>
                    </div>

                    <!-- Search Input -->
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input class="form-control border-start-0 search" data-target="available" placeholder="Search available items or routes (e.g. /sales)...">
                    </div>

                    <!-- Select List -->
                    <select multiple size="18" class="form-select list rounded-3 font-monospace shadow-sm" data-target="available" style="height: 380px; font-size: 13px;">
                    </select>
                    <small class="text-muted mt-2 d-block"><i class="fa-solid fa-keyboard me-1"></i>Hold <b>Ctrl</b> (Windows) or <b>Cmd</b> (Mac) to select multiple items.</small>
                </div>
            </div>

            <!-- Transfer Buttons Column -->
            <div class="col-lg-2 text-center d-flex flex-column align-items-center justify-content-center gap-3">
                <button type="button" 
                        class="btn btn-primary rounded-3 shadow-sm btn-assign w-100 py-2 fw-semibold" 
                        data-action="<?= Url::to(['assign', 'id' => (string)$model->name]) ?>" 
                        data-target="available" 
                        title="Add selected item(s) to this <?= strtolower($labels['Item']) ?>">
                    <span class="btn-text">Assign <i class="fa-solid fa-angles-right ms-1"></i></span>
                    <i class="fa-solid fa-spinner fa-spin ms-1 spinner-icon" style="display: none;"></i>
                </button>

                <button type="button" 
                        class="btn btn-outline-danger rounded-3 shadow-sm btn-assign w-100 py-2 fw-semibold" 
                        data-action="<?= Url::to(['remove', 'id' => (string)$model->name]) ?>" 
                        data-target="assigned" 
                        title="Remove selected item(s) from this <?= strtolower($labels['Item']) ?>">
                    <span class="btn-text"><i class="fa-solid fa-angles-left me-1"></i> Remove</span>
                    <i class="fa-solid fa-spinner fa-spin ms-1 spinner-icon" style="display: none;"></i>
                </button>

                <div class="text-muted small mt-2 px-2 text-center">
                    Select items on the left and click <b>Assign</b> to grant access.
                </div>
            </div>

            <!-- Assigned Column -->
            <div class="col-lg-5">
                <div class="card border rounded-4 bg-light bg-opacity-50 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-shield-halved text-success me-1"></i> Granted Permissions &amp; Routes
                        </label>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" id="assigned-count">0 items</span>
                    </div>

                    <!-- Filter pills -->
                    <div class="btn-group btn-group-sm w-100 mb-2 shadow-sm" role="group">
                        <input type="radio" class="btn-check filter-type" name="filter-assigned" id="as-all" value="" checked data-target="assigned">
                        <label class="btn btn-outline-secondary" for="as-all">All</label>

                        <?php if ($isRole): ?>
                            <input type="radio" class="btn-check filter-type" name="filter-assigned" id="as-role" value="role" data-target="assigned">
                            <label class="btn btn-outline-secondary" for="as-role">Roles</label>
                        <?php endif; ?>

                        <input type="radio" class="btn-check filter-type" name="filter-assigned" id="as-perm" value="permission" data-target="assigned">
                        <label class="btn btn-outline-secondary" for="as-perm">Permissions</label>

                        <input type="radio" class="btn-check filter-type" name="filter-assigned" id="as-route" value="route" data-target="assigned">
                        <label class="btn btn-outline-secondary" for="as-route">Routes (/)</label>
                    </div>

                    <!-- Search Input -->
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input class="form-control border-start-0 search" data-target="assigned" placeholder="Search granted items or routes...">
                    </div>

                    <!-- Select List -->
                    <select multiple size="18" class="form-select list rounded-3 font-monospace shadow-sm" data-target="assigned" style="height: 380px; font-size: 13px;">
                    </select>
                    <small class="text-muted mt-2 d-block"><i class="fa-solid fa-check-double text-success me-1"></i>Users holding this role automatically inherit all these routes and permissions.</small>
                </div>
            </div>
        </div>
    </div>
</div>
