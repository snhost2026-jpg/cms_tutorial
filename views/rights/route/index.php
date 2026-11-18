<?php

use mdm\admin\AnimateAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\YiiAsset;

/* @var $this yii\web\View */
/* @var $routes [] */

$this->title = 'System Routes Management';
$this->params['breadcrumbs'][] = ['label' => 'Users & Access', 'url' => ['/users/user/index']];
$this->params['breadcrumbs'][] = 'Routes';

AnimateAsset::register($this);
YiiAsset::register($this);

$opts = Json::htmlEncode([
    'routes' => $routes,
]);
$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
?>

<div class="user-rights-route-index py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-route text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Discover controller routes and register them as assignable RBAC permissions</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-user-shield me-1"></i> Assignments', ['/rights/assignment'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-shield-halved me-1"></i> Roles', ['/rights/role'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-key me-1"></i> Permissions', ['/rights/permission'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
        </div>
    </div>

    <!-- Quick Add Route Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-light bg-opacity-50">
        <label class="form-label fw-bold mb-2 text-dark small text-uppercase" style="letter-spacing: .05em;">
            <i class="fa-solid fa-plus-circle text-primary me-1"></i> Manually Register Custom Route / Wildcard
        </label>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted">
                <i class="fa-solid fa-link"></i>
            </span>
            <input id="inp-route" type="text" class="form-control border-start-0 font-monospace" placeholder="e.g. /sales/lead/export or /finance/*">
            <button class="btn btn-primary rounded-end-3 px-4 fw-semibold" id="btn-new" data-action="<?= Url::to(['create']) ?>">
                <span class="btn-text"><i class="fa-solid fa-plus me-1"></i> Add Route</span>
                <i class="fa-solid fa-spinner fa-spin ms-1 spinner-icon" style="display: none;"></i>
            </button>
        </div>
        <small class="text-muted mt-2 d-block">
            Tip: You can use wildcards like <code>/sales/*</code>, <code>/finance/record/*</code>, or <code>/*</code> to grant entire sections at once.
        </small>
    </div>

    <!-- Dual Listbox Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="row g-4 align-items-center">
            <!-- Available Column -->
            <div class="col-lg-5">
                <div class="card border rounded-4 bg-light bg-opacity-50 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-compass text-primary me-1"></i> Discovered System Routes
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" id="btn-refresh" data-action="<?= Url::to(['refresh']) ?>" title="Re-scan system controllers">
                            <i class="fa-solid fa-arrows-rotate me-1 refresh-icon"></i> Refresh Scan
                        </button>
                    </div>

                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input class="form-control border-start-0 search" data-target="available" placeholder="Search discovered routes...">
                    </div>

                    <select multiple size="18" class="form-select list rounded-3 font-monospace shadow-sm" data-target="available" style="height: 400px; font-size: 13px;">
                    </select>
                    <div class="d-flex justify-content-between align-items-center mt-2 text-muted small">
                        <span><i class="fa-solid fa-info-circle me-1"></i>Routes found in code</span>
                        <span id="available-count" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">0 items</span>
                    </div>
                </div>
            </div>

            <!-- Transfer Buttons Column -->
            <div class="col-lg-2 text-center d-flex flex-column align-items-center justify-content-center gap-3">
                <button type="button" 
                        class="btn btn-primary rounded-3 shadow-sm btn-assign w-100 py-2 fw-semibold" 
                        data-action="<?= Url::to(['assign']) ?>" 
                        data-target="available" 
                        title="Register selected route(s) in auth_item">
                    <span class="btn-text">Register <i class="fa-solid fa-angles-right ms-1"></i></span>
                    <i class="fa-solid fa-spinner fa-spin ms-1 spinner-icon" style="display: none;"></i>
                </button>

                <button type="button" 
                        class="btn btn-outline-danger rounded-3 shadow-sm btn-assign w-100 py-2 fw-semibold" 
                        data-action="<?= Url::to(['remove']) ?>" 
                        data-target="assigned" 
                        title="Unregister selected route(s)">
                    <span class="btn-text"><i class="fa-solid fa-angles-left me-1"></i> Unregister</span>
                    <i class="fa-solid fa-spinner fa-spin ms-1 spinner-icon" style="display: none;"></i>
                </button>

                <div class="text-muted small mt-2 px-2 text-center">
                    Registered routes can be directly assigned to users or bundled into roles.
                </div>
            </div>

            <!-- Registered Column -->
            <div class="col-lg-5">
                <div class="card border rounded-4 bg-light bg-opacity-50 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-circle-check text-success me-1"></i> Registered Active Routes
                        </label>
                        <span id="assigned-count" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">0 items</span>
                    </div>

                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input class="form-control border-start-0 search" data-target="assigned" placeholder="Search registered routes...">
                    </div>

                    <select multiple size="18" class="form-select list rounded-3 font-monospace shadow-sm" data-target="assigned" style="height: 400px; font-size: 13px; color: #0d6efd;">
                    </select>
                    <div class="d-flex justify-content-between align-items-center mt-2 text-muted small">
                        <span><i class="fa-solid fa-check-double text-success me-1"></i>Ready for assignment</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
