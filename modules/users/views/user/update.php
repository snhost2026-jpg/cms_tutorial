<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Edit User: ' . $user->username;
?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 fw-bold"><?= Html::encode($this->title) ?></h4>
            <span class="text-muted small">User ID #<?= (int)$user->id ?></span>
        </div>
        <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Users', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
    <div class="card-body p-4">
        <?php $form = ActiveForm::begin([
            'id' => 'user-update-form',
            'enableClientValidation' => true,
        ]); ?>

        <?= $form->errorSummary($model) ?>

        <div class="row g-3">
            <div class="col-md-6">
                <?= $form->field($model, 'username', [
                    'inputOptions' => [
                        'class' => 'form-control',
                        'id' => 'updateform-username',
                        'autocomplete' => 'off',
                    ],
                ])->textInput()->hint('<span id="username-live-hint" class="small text-muted">Must be unique across all system users.</span>') ?>
                <div id="username-live-error" class="text-danger small mt-1" style="display: none;"></div>
            </div>

            <div class="col-md-6">
                <?= $form->field($model, 'email')->textInput([
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ]) ?>
            </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <?= Html::submitButton('<i class="fa-solid fa-check me-1"></i> Save Changes', [
                'class' => 'btn btn-primary',
                'id' => 'btn-save-user',
            ]) ?>
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const usernameInput = document.getElementById('updateform-username');
    const liveError = document.getElementById('username-live-error');
    const liveHint = document.getElementById('username-live-hint');
    const saveBtn = document.getElementById('btn-save-user');
    const checkUrl = '<?= Url::to(['check-username']) ?>';
    const currentId = <?= (int)$user->id ?>;
    const originalUsername = <?= json_encode($user->username) ?>;

    let debounceTimer = null;

    if (usernameInput) {
        usernameInput.addEventListener('input', function () {
            const val = this.value.trim();
            clearTimeout(debounceTimer);

            if (val === '') {
                liveError.style.display = 'block';
                liveError.textContent = 'Username cannot be blank.';
                saveBtn.disabled = true;
                return;
            }

            if (val.toLowerCase() === originalUsername.toLowerCase()) {
                liveError.style.display = 'none';
                liveError.textContent = '';
                saveBtn.disabled = false;
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`${checkUrl}?username=${encodeURIComponent(val)}&id=${currentId}`)
                    .then(r => r.json())
                    .then(res => {
                        if (!res.available) {
                            liveError.style.display = 'block';
                            liveError.textContent = res.message || 'Username is already taken.';
                            saveBtn.disabled = true;
                        } else {
                            liveError.style.display = 'none';
                            liveError.textContent = '';
                            saveBtn.disabled = false;
                        }
                    })
                    .catch(() => {
                        liveError.style.display = 'none';
                        saveBtn.disabled = false;
                    });
            }, 300);
        });
    }
});
</script>
