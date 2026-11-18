<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'User Management';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0 fw-bold"><?= Html::encode($this->title) ?></h3>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="fa-solid fa-user-shield me-1"></i> User Rights', ['/rights/assignment'], ['class' => 'btn btn-outline-dark']) ?>
        <?= Html::a('<i class="fa-solid fa-shield-halved me-1"></i> Roles', ['/rights/role'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('<i class="fa-solid fa-key me-1"></i> Permissions', ['/rights/permission'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('<i class="fa-solid fa-route me-1"></i> Routes', ['/rights/route'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('<i class="fa-solid fa-user-plus me-1"></i> Add User', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<div id="page-ajax-flash" class="alert alert-success alert-dismissible fade show shadow-sm" style="display: none;" role="alert">
    <span id="page-ajax-flash-message"></span>
    <button type="button" class="btn-close" aria-label="Close" onclick="this.parentElement.style.display='none'"></button>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm">
        <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="width: 320px;" class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr id="user-row-<?= $user->id ?>">
                            <td class="text-muted fw-bold">#<?= $user->id ?></td>
                            <td>
                                <span class="fw-semibold text-dark user-username-text" id="user-name-<?= $user->id ?>">
                                    <?= Html::encode($user->username) ?>
                                </span>
                            </td>
                            <td id="user-email-<?= $user->id ?>"><?= Html::encode($user->email ?: '-') ?></td>
                            <td>
                                <?php 
                                $userRole = \app\modules\users\controllers\UserController::getUserRole($user->id); 
                                $isAssigned = ($userRole !== 'Not Assigned');
                                $assignmentUrl = Url::to(['/rights/assignment/view', 'id' => $user->id]);
                                ?>
                                <a href="<?= $assignmentUrl ?>" 
                                   class="badge bg-light text-dark border text-decoration-none" 
                                   data-bs-toggle="tooltip" 
                                   title="<?= $isAssigned ? 'Role: ' . Html::encode($userRole) . ' - Click to manage rights' : 'Click to assign role & rights' ?>">
                                    <i class="fa-solid <?= $isAssigned ? 'fa-shield-halved text-primary' : 'fa-circle-plus text-secondary' ?> me-1"></i><?= Html::encode($userRole) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ((int)$user->status === 10): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary btn-open-edit-modal" 
                                            data-id="<?= $user->id ?>" 
                                            data-username="<?= Html::encode($user->username) ?>" 
                                            data-email="<?= Html::encode($user->email ?: '') ?>"
                                            title="Edit Username & Email"
                                            data-bs-toggle="tooltip"
                                            aria-label="Edit Username & Email">
                                        <i class="fa-solid fa-user-pen"></i>
                                    </button>
                                    <?= Html::a('<i class="fa-solid fa-sliders"></i>', ['/rights/assignment/view', 'id' => $user->id], [
                                        'class' => 'btn btn-sm btn-outline-dark',
                                        'title' => 'Manage Rights & Permissions',
                                        'data-bs-toggle' => 'tooltip',
                                        'aria-label' => 'Manage Rights & Permissions'
                                    ]) ?>
                                    <?= Html::a('<i class="fa-solid fa-key"></i>', ['reset-password', 'id' => $user->id], [
                                        'class' => 'btn btn-sm btn-warning text-dark',
                                        'title' => 'Reset Password',
                                        'data-bs-toggle' => 'tooltip',
                                        'aria-label' => 'Reset Password'
                                    ]) ?>
                                    <?php if ((int)$user->status === 10): ?>
                                        <?= Html::a('<i class="fa-solid fa-user-xmark"></i>', ['toggle-status', 'id' => $user->id], [
                                            'class' => 'btn btn-sm btn-outline-danger',
                                            'title' => 'Deactivate User',
                                            'data-bs-toggle' => 'tooltip',
                                            'aria-label' => 'Deactivate User'
                                        ]) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fa-solid fa-user-check"></i>', ['toggle-status', 'id' => $user->id], [
                                            'class' => 'btn btn-sm btn-outline-success',
                                            'title' => 'Activate User',
                                            'data-bs-toggle' => 'tooltip',
                                            'aria-label' => 'Activate User'
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Username Modal -->
<div class="modal fade" id="editUsernameModal" tabindex="-1" aria-labelledby="editUsernameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold" id="editUsernameModalLabel">
                    <i class="fa-solid fa-user-pen text-primary me-2"></i>Edit Username
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="edit-username-modal-form" autocomplete="off">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                <input type="hidden" id="modal-user-id" name="id" value="">
                
                <div class="modal-body p-4">
                    <div id="modal-global-alert" class="alert alert-danger py-2 small" style="display: none;"></div>

                    <div class="mb-3">
                        <label for="modal-username-input" class="form-label fw-semibold">
                            Username <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal-username-input" name="UserUpdateForm[username]" required>
                        <!-- Small message when username is already taken -->
                        <div id="modal-username-error" class="text-danger small mt-1 fw-semibold" style="display: none;"></div>
                        <div id="modal-username-hint" class="text-muted small mt-1">Username must be unique across all system users.</div>
                    </div>

                    <div class="mb-2">
                        <label for="modal-email-input" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" id="modal-email-input" name="UserUpdateForm[email]">
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4 bg-light rounded-bottom-4 d-flex justify-content-between">
                    <a id="modal-full-edit-link" href="#" class="btn btn-link text-decoration-none p-0 small text-muted">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Full Edit Page
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3" id="modal-save-btn">
                            <i class="fa-solid fa-check me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('editUsernameModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('edit-username-modal-form');
    const userIdInput = document.getElementById('modal-user-id');
    const usernameInput = document.getElementById('modal-username-input');
    const emailInput = document.getElementById('modal-email-input');
    const usernameError = document.getElementById('modal-username-error');
    const usernameHint = document.getElementById('modal-username-hint');
    const globalAlert = document.getElementById('modal-global-alert');
    const saveBtn = document.getElementById('modal-save-btn');
    const fullEditLink = document.getElementById('modal-full-edit-link');
    const pageFlash = document.getElementById('page-ajax-flash');
    const pageFlashMsg = document.getElementById('page-ajax-flash-message');

    const checkUrl = '<?= Url::to(['check-username']) ?>';
    const updateBaseUrl = '<?= Url::to(['update']) ?>';

    let originalUsername = '';
    let debounceTimer = null;

    // Open modal when edit button or pencil is clicked
    document.querySelectorAll('.btn-open-edit-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const username = this.getAttribute('data-username') || '';
            const email = this.getAttribute('data-email') || '';

            originalUsername = username;
            userIdInput.value = id;
            usernameInput.value = username;
            emailInput.value = email;

            usernameError.style.display = 'none';
            usernameError.textContent = '';
            usernameInput.classList.remove('is-invalid', 'is-valid');
            globalAlert.style.display = 'none';
            saveBtn.disabled = false;

            fullEditLink.href = `${updateBaseUrl}?id=${encodeURIComponent(id)}`;

            modal.show();
            setTimeout(() => usernameInput.focus(), 300);
        });
    });

    // Real-time live check for uniqueness with debounce
    usernameInput.addEventListener('input', function () {
        const val = this.value.trim();
        const currentId = userIdInput.value;
        clearTimeout(debounceTimer);

        if (val === '') {
            usernameInput.classList.add('is-invalid');
            usernameInput.classList.remove('is-valid');
            usernameError.style.display = 'block';
            usernameError.textContent = 'Username cannot be blank.';
            saveBtn.disabled = true;
            return;
        }

        // Same username as currently saved for this user
        if (val.toLowerCase() === originalUsername.toLowerCase()) {
            usernameInput.classList.remove('is-invalid', 'is-valid');
            usernameError.style.display = 'none';
            usernameError.textContent = '';
            saveBtn.disabled = false;
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`${checkUrl}?username=${encodeURIComponent(val)}&id=${encodeURIComponent(currentId)}`)
                .then(r => r.json())
                .then(res => {
                    if (!res.available) {
                        usernameInput.classList.add('is-invalid');
                        usernameInput.classList.remove('is-valid');
                        usernameError.style.display = 'block';
                        usernameError.textContent = res.message || 'Username is already taken.';
                        saveBtn.disabled = true;
                    } else {
                        usernameInput.classList.remove('is-invalid');
                        usernameInput.classList.add('is-valid');
                        usernameError.style.display = 'none';
                        usernameError.textContent = '';
                        saveBtn.disabled = false;
                    }
                })
                .catch(() => {
                    usernameError.style.display = 'none';
                    saveBtn.disabled = false;
                });
        }, 250);
    });

    // Handle form submit via AJAX
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const currentId = userIdInput.value;
        const newUsername = usernameInput.value.trim();
        const newEmail = emailInput.value.trim();

        if (newUsername === '') {
            usernameError.style.display = 'block';
            usernameError.textContent = 'Username cannot be blank.';
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';

        const formData = new FormData(form);

        fetch(`${updateBaseUrl}?id=${encodeURIComponent(currentId)}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(r => r.json())
        .then(res => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Changes';

            if (res.success) {
                // Update text on page
                const nameEl = document.getElementById(`user-name-${currentId}`);
                if (nameEl) nameEl.textContent = res.user.username;

                const emailEl = document.getElementById(`user-email-${currentId}`);
                if (emailEl) emailEl.textContent = res.user.email || '-';

                // Update data attributes on buttons in that row
                document.querySelectorAll(`.btn-open-edit-modal[data-id="${currentId}"]`).forEach(b => {
                    b.setAttribute('data-username', res.user.username);
                    b.setAttribute('data-email', res.user.email || '');
                });

                // Highlight updated row briefly
                const row = document.getElementById(`user-row-${currentId}`);
                if (row) {
                    row.classList.add('table-success');
                    setTimeout(() => row.classList.remove('table-success'), 2000);
                }

                modal.hide();

                // Show flash
                pageFlashMsg.textContent = `Username updated successfully to "${res.user.username}".`;
                pageFlash.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                if (res.errors && res.errors.username) {
                    usernameInput.classList.add('is-invalid');
                    usernameError.style.display = 'block';
                    usernameError.textContent = res.errors.username[0] || 'Username is already taken.';
                } else {
                    globalAlert.textContent = res.message || 'Failed to update user.';
                    globalAlert.style.display = 'block';
                }
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save Changes';
            globalAlert.textContent = 'An error occurred while saving. Please try again.';
            globalAlert.style.display = 'block';
        });
    });
});
</script>
