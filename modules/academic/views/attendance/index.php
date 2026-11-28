<?php

use app\modules\academic\models\StudentAttendance;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $sections array */
/* @var $section app\modules\course\models\Section|null */
/* @var $subjects array */
/* @var $subject app\modules\course\models\Subject|null */
/* @var $students app\modules\student\models\StuMaster[] */
/* @var $attendanceByStudent array */
/* @var $date string */

$this->title = 'Take Attendance';
$this->params['breadcrumbs'][] = ['label' => 'Attendance', 'url' => ['/academic/attendance/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="attendance-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
            <p class="text-muted mb-0">Choose a section, subject, and date to record student attendance.</p>
        </div>
        <?= Html::a('View attendance history', ['history'], ['class' => 'btn btn-outline-primary']) ?>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <?= Html::beginForm(['index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
                <div class="col-md-8">
                    <?= Html::label('Section', 'section_id', ['class' => 'form-label fw-semibold']) ?>
                    <?= Html::dropDownList('section_id', $section ? $section->id : null, $sections, [
                        'id' => 'section_id',
                        'class' => 'form-select',
                        'prompt' => 'Choose a section',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= Html::submitButton('Load section', ['class' => 'btn btn-outline-primary w-100']) ?>
                </div>
            <?= Html::endForm() ?>

            <?php if ($section): ?>
                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <div class="text-muted small">Selected section</div>
                        <div class="fw-semibold"><?= Html::encode($sections[$section->id] ?? $section->name) ?></div>
                    </div>
                    <?= Html::a('Change section', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                </div>

                <?= Html::beginForm(['index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
                    <?= Html::hiddenInput('section_id', $section->id) ?>
                    <div class="col-md-6">
                        <?= Html::label('Subject', 'subject_id', ['class' => 'form-label fw-semibold']) ?>
                        <?= Html::dropDownList('subject_id', $subject ? $subject->id : null, $subjects, [
                            'id' => 'subject_id',
                            'class' => 'form-select',
                            'prompt' => 'Choose a subject',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= Html::label('Attendance date', 'date', ['class' => 'form-label fw-semibold']) ?>
                        <?= Html::input('date', 'date', $date, [
                            'id' => 'date',
                            'class' => 'form-control',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= Html::submitButton('Load students', ['class' => 'btn btn-primary w-100']) ?>
                    </div>
                <?= Html::endForm() ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($section && $subject): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1"><?= Html::encode($subject->name) ?></h5>
                        <div class="text-muted small">
                            <?= Html::encode($sections[$section->id] ?? $section->name) ?>
                            <span class="mx-1">·</span>
                            <?= Html::encode(Yii::$app->formatter->asDate($date, 'php:M j, Y')) ?>
                        </div>
                    </div>
                    <span class="badge text-bg-light border"><?= count($students) ?> active students</span>
                </div>
            </div>

            <?php if (!$students): ?>
                <div class="card-body p-4 text-muted">There are no active students assigned to this section.</div>
            <?php else: ?>
                <?= Html::beginForm(['index'], 'post', ['class' => 'attendance-form']) ?>
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                    <?= Html::hiddenInput('section_id', $section->id) ?>
                    <?= Html::hiddenInput('subject_id', $subject->id) ?>
                    <?= Html::hiddenInput('attendance_date', $date) ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 70px;">#</th>
                                    <th style="width: 180px;">Registration No.</th>
                                    <th>Student</th>
                                    <th style="width: 240px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <?php
                                    $studentId = (int) $student->stu_master_id;
                                    $info = $student->info;
                                    ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?= $index + 1 ?></td>
                                        <td class="font-monospace"><?= Html::encode($info ? ($info->stu_unique_id ?: '-') : '-') ?></td>
                                        <td class="fw-semibold"><?= Html::encode($info ? $info->fullName : '(No profile)') ?></td>
                                        <td>
                                            <?= Html::dropDownList(
                                                'attendance[' . $studentId . ']',
                                                isset($attendanceByStudent[$studentId]) ? $attendanceByStudent[$studentId] : '',
                                                StudentAttendance::statusOptions(),
                                                [
                                                    'class' => 'form-select form-select-sm',
                                                    'prompt' => 'Select status',
                                                    'required' => true,
                                                ]
                                            ) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end p-3 border-top">
                        <?= Html::submitButton('Save attendance', ['class' => 'btn btn-primary px-4']) ?>
                    </div>
                <?= Html::endForm() ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
