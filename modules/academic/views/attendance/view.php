<?php

use app\modules\academic\models\StudentAttendance;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $section app\modules\course\models\Section */
/* @var $subject app\modules\course\models\Subject */
/* @var $sectionLabel string */
/* @var $date string */
/* @var $records array */

$this->title = 'Attendance Details';
$this->params['breadcrumbs'][] = ['label' => 'Attendance', 'url' => ['/academic/attendance/index']];
$this->params['breadcrumbs'][] = ['label' => 'Attendance History', 'url' => ['history']];
$this->params['breadcrumbs'][] = $this->title;

$counts = array_fill_keys(array_keys(StudentAttendance::statusOptions()), 0);
foreach ($records as $record) {
    if (isset($counts[$record['status']])) {
        $counts[$record['status']]++;
    }
}
$statusClasses = [
    StudentAttendance::STATUS_PRESENT => 'success',
    StudentAttendance::STATUS_ABSENT => 'danger',
    StudentAttendance::STATUS_LATE => 'warning',
    StudentAttendance::STATUS_EXCUSED => 'secondary',
];
?>

<div class="attendance-details">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
            <p class="text-muted mb-0">
                <?= Html::encode($sectionLabel) ?> · <?= Html::encode($subject->name) ?>
                · <?= Html::encode(Yii::$app->formatter->asDate($date, 'php:M j, Y')) ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('Attendance History', ['history'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('Edit Attendance', [
                'index',
                'section_id' => $section->id,
                'subject_id' => $subject->id,
                'date' => $date,
            ], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach (StudentAttendance::statusOptions() as $status => $label): ?>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="text-muted small"><?= Html::encode($label) ?></div>
                        <div class="fs-4 fw-bold"><?= (int) $counts[$status] ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 70px;">#</th>
                        <th style="width: 200px;">Registration No.</th>
                        <th>Student</th>
                        <th style="width: 180px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$records): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No attendance records were found for this session.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $index => $record): ?>
                            <?php
                            $nameParts = array_filter(
                                [$record['first_name'], $record['middle_name'], $record['last_name']],
                                function ($part) {
                                    return $part !== null && $part !== '';
                                }
                            );
                            $name = $nameParts ? implode(' ', $nameParts) : '(no name)';
                            $statusLabel = StudentAttendance::statusOptions()[$record['status']] ?? $record['status'];
                            ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $index + 1 ?></td>
                                <td class="font-monospace"><?= Html::encode($record['registration_no'] ?: '-') ?></td>
                                <td class="fw-semibold"><?= Html::encode($name) ?></td>
                                <td>
                                    <span class="badge text-bg-<?= Html::encode($statusClasses[$record['status']] ?? 'secondary') ?>">
                                        <?= Html::encode($statusLabel) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
