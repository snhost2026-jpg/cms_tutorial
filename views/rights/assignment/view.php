<?php

use mdm\admin\AnimateAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\YiiAsset;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\Assignment */
/* @var $fullnameField string */

$userName = $model->{$usernameField};
if (!empty($fullnameField)) {
    $userName .= ' (' . ArrayHelper::getValue($model, $fullnameField) . ')';
}
$encodedUserName = Html::encode($userName);

$this->title = 'User Rights: ' . $encodedUserName;
$this->params['breadcrumbs'][] = ['label' => 'Users & Access', 'url' => ['/users/user/index']];
$this->params['breadcrumbs'][] = ['label' => 'Assignments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $encodedUserName;

AnimateAsset::register($this);
YiiAsset::register($this);

$itemsData = $model->getItems();
$opts = Json::htmlEncode([
    'items' => $itemsData,
]);
$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
?>

<div class="user-assignment-view py-2">
    <!-- Header with Breadcrumbs & Actions -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="rounded-circle bg-primary text-white fw-bold d-inline-flex align-items-center justify-content-center shadow-sm" style="width:38px;height:38px;font-size:16px;">
                    <?= strtoupper(substr((string)$userName, 0, 1) ?: 'U') ?>
                </span>
                <h3 class="fw-bold mb-0 text-dark"><?= $encodedUserName ?></h3>
                <span class="badge bg-light text-muted border ms-1">User #<?= (int)$model->id ?></span>
            </div>
            <p class="text-muted small mb-0">Assign system roles, module permissions, or specific page routes directly to this user</p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Assignments', ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-users me-1"></i> User List', ['/users/user/index'], ['class' => 'btn btn-outline-dark rounded-3']) ?>
        </div>
    </div>

    <!-- Alert / Tips banner -->
    <div class="alert alert-light border shadow-sm rounded-4 mb-4 d-flex align-items-start gap-3 p-3">
        <i class="fa-solid fa-circle-info text-primary fs-4 mt-1"></i>
        <div class="small text-muted flex-grow-1">
            <b class="text-dark">How user rights work:</b>
            <div class="mt-1">
                &bull; <b>Roles</b>: Bundles of permissions (e.g. <code>admin</code>, <code>employee</code>, <code>accountant</code>).<br>
                &bull; <b>Permissions</b>: High-level capabilities (e.g. <code>manageSales</code>, <code>createLead</code>).<br>
                &bull; <b>Specific Routes</b>: Direct URLs (e.g. <code>/sales/lead/create</code>, <code>/finance/*</code>, <code>/joining/admin/*</code>). Assigning a route grants access to that specific page or section directly.
            </div>
        </div>
    </div>

    <!-- Dual Listbox Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="row g-4 align-items-center">
            <!-- Available Column -->
            <div class="col-lg-5">
                <div class="card border rounded-4 bg-light bg-opacity-50 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-boxes-stacked text-primary me-1"></i> Available Rights &amp; Routes
                        </label>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1" id="available-count">0 items</span>
                    </div>

                    <!-- Type Filter Pills -->
                    <div class="btn-group btn-group-sm w-100 mb-2 shadow-sm" role="group" aria-label="Available filter">
                        <input type="radio" class="btn-check filter-type" name="filter-available" id="av-all" value="" checked data-target="available">
                        <label class="btn btn-outline-secondary" for="av-all">All</label>

                        <input type="radio" class="btn-check filter-type" name="filter-available" id="av-role" value="role" data-target="available">
                        <label class="btn btn-outline-secondary" for="av-role">Roles</label>

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
                        data-action="<?= Url::to(['assign', 'id' => (string)$model->id]) ?>" 
                        data-target="available" 
                        title="Assign selected item(s) to this user">
                    <span class="btn-text">Assign <i class="fa-solid fa-angles-right ms-1"></i></span>
                    <i class="fa-solid fa-spinner fa-spin ms-1 spinner-icon" style="display: none;"></i>
                </button>

                <button type="button" 
                        class="btn btn-outline-danger rounded-3 shadow-sm btn-assign w-100 py-2 fw-semibold" 
                        data-action="<?= Url::to(['revoke', 'id' => (string)$model->id]) ?>" 
                        data-target="assigned" 
                        title="Remove selected item(s) from this user">
                    <span class="btn-text"><i class="fa-solid fa-angles-left me-1"></i> Remove</span>
                    <i class="fa-solid fa-spinner fa-spin ms-1 spinner-icon" style="display: none;"></i>
                </button>

                <div class="text-muted small mt-2 px-2 text-center">
                    Select one or more items and click <b>Assign</b> or <b>Remove</b>.
                </div>
            </div>

            <!-- Assigned Column -->
            <div class="col-lg-5">
                <div class="card border rounded-4 bg-light bg-opacity-50 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-shield-halved text-success me-1"></i> Assigned to This User
                        </label>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" id="assigned-count">0 items</span>
                    </div>

                    <!-- Type Filter Pills -->
                    <div class="btn-group btn-group-sm w-100 mb-2 shadow-sm" role="group" aria-label="Assigned filter">
                        <input type="radio" class="btn-check filter-type" name="filter-assigned" id="as-all" value="" checked data-target="assigned">
                        <label class="btn btn-outline-secondary" for="as-all">All</label>

                        <input type="radio" class="btn-check filter-type" name="filter-assigned" id="as-role" value="role" data-target="assigned">
                        <label class="btn btn-outline-secondary" for="as-role">Roles</label>

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
                        <input class="form-control border-start-0 search" data-target="assigned" placeholder="Search assigned items or routes...">
                    </div>

                    <!-- Select List -->
                    <select multiple size="18" class="form-select list rounded-3 font-monospace shadow-sm" data-target="assigned" style="height: 380px; font-size: 13px;">
                    </select>
                    <small class="text-muted mt-2 d-block"><i class="fa-solid fa-check-double text-success me-1"></i>These permissions and routes are currently active for this user.</small>
                </div>
            </div>
        </div>
    </div>
</div>
