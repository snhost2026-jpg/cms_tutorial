<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $role array */
/* @var $items array */

$roleName = $role['name'];
$this->title = 'Manage Role Access: ' . $roleName;
$this->params['breadcrumbs'][] = ['label' => 'Auth Assignments', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Role Access', 'url' => ['roles']];
$this->params['breadcrumbs'][] = $roleName;

$opts = Json::htmlEncode([
    'items' => $items,
    'assignUrl' => Url::to(['assign-role', 'name' => $roleName]),
    'revokeUrl' => Url::to(['revoke-role', 'name' => $roleName]),
]);
?>

<div class="auth-assignment-role">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Role Authorization</span>
                <span class="badge bg-light text-muted border font-monospace">System Role: <?= Html::encode($roleName) ?></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= Html::encode(ucfirst($roleName)) ?> Role</h3>
            <div class="text-muted small"><?= Html::encode($role['description'] ?: 'Configure specific page routes and permissions for this role.') ?></div>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Roles List', ['roles'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            <?= Html::a('<i class="fa-solid fa-users me-1"></i> User Assignments', ['index'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-dark fs-6">
                <i class="fa-solid fa-sliders text-primary me-1"></i> Dual-Listbox Role Access Manager
            </span>
            <div class="small text-muted">
                Select page routes on the left and click <strong>&gt;&gt; Assign</strong> to grant specific page access to all users with the <strong><?= Html::encode($roleName) ?></strong> role.
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 align-items-center">
                <!-- 1. AVAILABLE COLUMN -->
                <div class="col-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="fw-bold small text-uppercase text-muted mb-0">
                            <i class="fa-solid fa-list-check me-1"></i> Available Page Routes & Permissions
                        </label>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill" id="count-available">0 items</span>
                    </div>
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control border-start-0 search-box" data-target="available" placeholder="Search available items...">
                    </div>
                    <select multiple size="20" class="form-select font-monospace small list-box" data-target="available" style="height: 420px; border-radius: 12px;">
                    </select>
                </div>

                <!-- 2. ACTION BUTTONS COLUMN -->
                <div class="col-md-2 text-center d-flex flex-column align-items-center justify-content-center gap-3">
                    <button type="button" class="btn btn-success fw-bold px-3 py-2 w-100 shadow-sm btn-assign-action" data-action="assign">
                        <i class="fa-solid fa-angles-right me-1"></i> Assign <span class="spinner-border spinner-border-sm d-none" id="spin-assign"></span>
                    </button>
                    <button type="button" class="btn btn-danger fw-bold px-3 py-2 w-100 shadow-sm btn-assign-action" data-action="revoke">
                        <i class="fa-solid fa-angles-left me-1"></i> Remove <span class="spinner-border spinner-border-sm d-none" id="spin-revoke"></span>
                    </button>
                    <div class="text-muted small mt-2" style="font-size: 11px;">
                        Tip: Hold <kbd>Ctrl</kbd> or <kbd>Shift</kbd> to select multiple routes
                    </div>
                </div>

                <!-- 3. ASSIGNED COLUMN -->
                <div class="col-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="fw-bold small text-uppercase text-success mb-0">
                            <i class="fa-solid fa-circle-check me-1"></i> Assigned to Role: <?= Html::encode($roleName) ?>
                        </label>
                        <span class="badge bg-success-subtle text-success rounded-pill" id="count-assigned">0 items</span>
                    </div>
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control border-start-0 search-box" data-target="assigned" placeholder="Search assigned items...">
                    </div>
                    <select multiple size="20" class="form-select font-monospace small list-box" data-target="assigned" style="height: 420px; border-radius: 12px;">
                    </select>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var _opts = <?= $opts ?>;

    function renderList(target) {
        var $select = document.querySelector('select.list-box[data-target="' + target + '"]');
        var query = (document.querySelector('input.search-box[data-target="' + target + '"]').value || '').toLowerCase();
        $select.innerHTML = '';

        var groups = {
            role: { label: 'Child Roles', optgroup: document.createElement('optgroup'), count: 0 },
            permission: { label: 'System Permissions', optgroup: document.createElement('optgroup'), count: 0 },
            route: { label: 'Page Routes (CMS Style)', optgroup: document.createElement('optgroup'), count: 0 }
        };

        groups.role.optgroup.label = 'Child Roles';
        groups.permission.optgroup.label = 'System Permissions';
        groups.route.optgroup.label = 'Page Routes (CMS Style)';

        var total = 0;
        var itemsObj = _opts.items[target] || {};

        for (var name in itemsObj) {
            if (!itemsObj.hasOwnProperty(name)) continue;
            var groupType = itemsObj[name] || 'route';
            if (!groups[groupType]) groupType = 'route';

            if (!query || name.toLowerCase().indexOf(query) !== -1) {
                var opt = document.createElement('option');
                opt.value = name;
                opt.textContent = name;
                groups[groupType].optgroup.appendChild(opt);
                groups[groupType].count++;
                total++;
            }
        }

        ['route', 'permission', 'role'].forEach(function(k) {
            if (groups[k].count > 0) {
                $select.appendChild(groups[k].optgroup);
            }
        });

        var countBadge = document.getElementById('count-' + target);
        if (countBadge) {
            countBadge.textContent = total + ' items';
        }
    }

    function refreshBoth() {
        renderList('available');
        renderList('assigned');
    }

    // Search filter event listeners
    document.querySelectorAll('.search-box').forEach(function(input) {
        input.addEventListener('keyup', function() {
            renderList(this.getAttribute('data-target'));
        });
    });

    // Assign & Revoke click handlers
    document.querySelectorAll('.btn-assign-action').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var action = this.getAttribute('data-action');
            var isAssign = (action === 'assign');
            var targetList = isAssign ? 'available' : 'assigned';
            var select = document.querySelector('select.list-box[data-target="' + targetList + '"]');
            var selectedOptions = Array.from(select.selectedOptions).map(function(o) { return o.value; });

            if (!selectedOptions.length) {
                alert('Please select at least one item from the ' + (isAssign ? 'Available' : 'Assigned') + ' list.');
                return;
            }

            var spinner = document.getElementById('spin-' + action);
            if (spinner) spinner.classList.remove('d-none');
            this.disabled = true;

            var url = isAssign ? _opts.assignUrl : _opts.revokeUrl;

            var formData = new FormData();
            selectedOptions.forEach(function(item) {
                formData.append('items[]', item);
            });

            // Include CSRF token
            var csrfParam = '<?= Yii::$app->request->csrfParam ?>';
            var csrfToken = '<?= Yii::$app->request->csrfToken ?>';
            formData.append(csrfParam, csrfToken);

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.available && data.assigned) {
                    _opts.items = data;
                    refreshBoth();
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(function(err) {
                console.error(err);
                alert('An error occurred while saving role assignments.');
            })
            .finally(function() {
                if (spinner) spinner.classList.add('d-none');
                btn.disabled = false;
            });
        });
    });

    refreshBoth();
});
</script>
