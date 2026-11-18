<?php

use yii\helpers\Html;

$this->title = 'User Login History';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= Html::encode($this->title) ?></h3>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Login At</th>
                    <th>Logout At</th>
                    <th>IP Address</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $row): ?>
                    <tr>
                        <td><?= $row->id ?></td>
                        <td><?= Html::encode($row->user->username ?? '-') ?></td>
                        <td><?= !empty($row->login_at) ? date('d M Y h:i A', strtotime($row->login_at)) : '-' ?></td>
                        <td><?= !empty($row->logout_at) ? date('d M Y h:i A', strtotime($row->logout_at)) : '-' ?></td>
                        <td><?= Html::encode($row->ip_address ?: '-') ?></td>
                        <td><?= Html::encode($row->location ?: '-') ?></td>
                        <td>
                            <?php if ((int)$row->is_active === 1): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Closed</span>
                            <?php endif; ?>
                        </td>
                        <td><?= Html::encode($row->logout_reason ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
