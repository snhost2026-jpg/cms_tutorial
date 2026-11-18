<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $roles array */

$this->title = 'Role Access & Permissions';
$this->params['breadcrumbs'][] = ['label' => 'Auth Assignments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-assignment-roles">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0 text-dark"><?= Html::encode($this->title) ?></h3>
            <div class="text-muted small">Manage specific page routes, controller actions, and permissions granted to each system role.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::beginForm(['sync-routes'], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton('<i class="fa-solid fa-arrows-rotate me-1"></i> Sync Page Routes', [
                'class' => 'btn btn-outline-primary btn-sm',
                'title' => 'Populate standard CRM routes into auth_item table',
                'data-bs-toggle' => 'tooltip',
            ]) ?>
            <?= Html::endForm() ?>

            <?= Html::a('<i class="fa-solid fa-users me-1"></i> User Management', ['/users/user/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>

            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                <i class="fa-solid fa-plus me-1"></i> Create Role
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['index']) ?>">
                <i class="fa-solid fa-users me-1"></i> User Assignments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="<?= Url::to(['roles']) ?>">
                <i class="fa-solid fa-shield-halved me-1"></i> Role Access & Permissions
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-dark fs-6">
                <i class="fa-solid fa-layer-group text-primary me-1"></i> Defined System Roles (<?= count($roles) ?>)
            </span>
            <span class="text-muted small">
                Click <strong>Access</strong> to configure specific page access and permissions for any role using the dual-listbox manager.
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 220px;">Role Name</th>
                        <th>Description</th>
                        <th style="width: 140px;" class="text-center">Assigned Users</th>
                        <th>Granted Page Routes & Permissions</th>
                        <th style="width: 130px;" class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($roles as $roleName => $r): ?>
                        <tr>
                            <td class="text-muted fw-bold"><?= $i++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill fw-semibold font-monospace">
                                        <i class="fa-solid fa-shield-halved me-1"></i><?= Html::encode($r['name']) ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="text-dark small">
                                    <?= Html::encode($r['description'] ?: 'Standard system role.') ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                    <i class="fa-solid fa-user me-1 text-muted"></i><?= (int)$r['userCount'] ?> user<?= (int)$r['userCount'] === 1 ? '' : 's' ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                        <?= (int)$r['routeCount'] ?> page route<?= (int)$r['routeCount'] === 1 ? '' : 's' ?>
                                    </span>
                                    <?php if ((int)$r['permCount'] > 0): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 small">
                                            <?= (int)$r['permCount'] ?> permission<?= (int)$r['permCount'] === 1 ? '' : 's' ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($r['sampleRoutes'])): ?>
                                        <span class="text-muted small ms-1" style="font-size: 11px;">
                                            (<?= implode(', ', array_map(function($route) { return Html::encode($route); }, $r['sampleRoutes'])) ?><?= (int)$r['routeCount'] > count($r['sampleRoutes']) ? '…' : '' ?>)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?= Url::to(['role', 'name' => $r['name']]) ?>" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="Manage Page Access for <?= Html::encode($r['name']) ?>" 
                                   data-bs-toggle="tooltip">
                                    <i class="fa-solid fa-sliders me-1"></i> Access
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Create New Role -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="createRoleModalLabel">
                    <i class="fa-solid fa-shield-halved text-primary me-2"></i>Create New System Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= Html::beginForm(['create-role'], 'post', ['autocomplete' => 'off']) ?>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="role-name-input" class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="role-name-input" name="name" placeholder="e.g. accountant, sales_executive" required>
                    <div class="text-muted small mt-1">Unique identifier for the role (lowercase, letters and hyphens/underscores).</div>
                </div>
                <div class="mb-2">
                    <label for="role-desc-input" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" id="role-desc-input" name="description" rows="2" placeholder="Brief summary of duties and responsibilities..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-4 bg-light rounded-bottom-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm fw-bold">
                    <i class="fa-solid fa-plus me-1"></i> Create Role
                </button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
