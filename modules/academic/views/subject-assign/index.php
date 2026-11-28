<?php

use app\modules\academic\models\SubjectAssign;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var SubjectAssign $model */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $courseOptions */
/** @var array $batchOptions */
/** @var array $sectionOptions */
/** @var array $subjectOptions */
/** @var array $teacherOptions */

$this->title = 'Subject Assign Teacher';
$this->params['breadcrumbs'][] = ['label' => 'Academic', 'url' => ['/academic/attendance/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
        <p class="text-muted mb-0">Assign a subject to a teacher for a course, batch, and section.</p>
    </div>
</div>

<?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
    <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show" role="alert">
        <?= Html::encode($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endforeach; ?>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <?php $form = ActiveForm::begin(['method' => 'post']); ?>
        <div class="row g-3">
            <div class="col-md-6">
                <?= $form->field($model, 'course_id')->dropDownList($courseOptions, [
                    'prompt' => 'Select Course',
                    'id' => 'subjectassign-course_id',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'Batch_id')->dropDownList($batchOptions, [
                    'prompt' => 'Select Batch',
                    'id' => 'subjectassign-batch_id',
                    'disabled' => $model->course_id ? false : true,
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'section_id')->dropDownList($sectionOptions, [
                    'prompt' => 'Select Section',
                    'id' => 'subjectassign-section_id',
                    'disabled' => $model->Batch_id ? false : true,
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'subject_code')->dropDownList($subjectOptions, [
                    'prompt' => 'Select Subject',
                    'id' => 'subjectassign-subject_code',
                    'disabled' => $model->course_id ? false : true,
                ]) ?>
            </div>
            <div class="col-md-12">
                <?= $form->field($model, 'employ_master_id')->dropDownList($teacherOptions, [
                    'prompt' => 'Select Teacher',
                ]) ?>
            </div>
        </div>
        <div class="d-flex gap-2 mt-2">
            <?= Html::submitButton('Assign Subject', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="fw-bold mb-0">Assigned Subjects</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Subject</th>
                <th>Course</th>
                <th>Batch</th>
                <th>Section</th>
                <th>Teacher</th>
                <th class="text-end">Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($dataProvider->getModels() as $assignment): ?>
                <?php
                $teacherName = $assignment->employee && $assignment->employee->info
                    ? $assignment->employee->info->fullName
                    : 'Employee #' . $assignment->employ_master_id;
                ?>
                <tr>
                    <td>
                        <?= Html::encode($assignment->subject_code) ?>
                    </td>
                    <td><?= Html::encode($assignment->course ? $assignment->course->name : '-') ?></td>
                    <td><?= Html::encode($assignment->batch ? $assignment->batch->name : '-') ?></td>
                    <td><?= Html::encode($assignment->section ? $assignment->section->name : '-') ?></td>
                    <td><?= Html::encode($teacherName) ?></td>
                    <td class="text-end">
                        <?= Html::a('Remove', ['delete', 'id' => $assignment->id], [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data-confirm' => 'Remove this subject assignment?',
                            'data-method' => 'post',
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$dataProvider->getModels()) : ?>
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No subject assignments found.</td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const batchByCourse = <?= json_encode(array_reduce(
        \app\modules\course\models\Batch::find()->select(['id', 'course_id'])->asArray()->all(),
        function ($result, $row) {
            $result[(int) $row['id']] = (int) $row['course_id'];
            return $result;
        },
        []
    )) ?>;
    const sectionByCourseAndBatch = <?= json_encode(array_reduce(
        \app\modules\course\models\Section::find()->select(['id', 'course_id', 'batch_id'])->asArray()->all(),
        function ($result, $row) {
            $result[(int) $row['id']] = [(int) $row['course_id'], (int) $row['batch_id']];
            return $result;
        },
        []
    )) ?>;
    const subjectByCourse = <?= json_encode(array_reduce(
        \app\modules\course\models\Subject::find()->select(['id', 'code', 'course_id'])->asArray()->all(),
        function ($result, $row) {
            $result[$row['code'] ?: (string) $row['id']] = (int) $row['course_id'];
            return $result;
        },
        []
    )) ?>;

    const course = document.getElementById('subjectassign-course_id');
    const batch = document.getElementById('subjectassign-batch_id');
    const section = document.getElementById('subjectassign-section_id');
    const subject = document.getElementById('subjectassign-subject_code');

    function filterOptions() {
        const courseId = course ? course.value : '';
        const batchId = batch ? batch.value : '';

        if (batch) {
            batch.disabled = courseId === '';
            Array.from(batch.options).forEach(function (option) {
                if (!option.value) return;
                option.hidden = batchByCourse[option.value] !== parseInt(courseId, 10);
            });
            if (batchId && batchByCourse[batchId] !== parseInt(courseId, 10)) batch.value = '';
        }

        if (section) {
            section.disabled = courseId === '' || !batch.value;
            Array.from(section.options).forEach(function (option) {
                if (!option.value) return;
                const placement = sectionByCourseAndBatch[option.value];
                option.hidden = !placement || placement[0] !== parseInt(courseId, 10) || placement[1] !== parseInt(batch.value, 10);
            });
        }

        if (subject) {
            subject.disabled = courseId === '';
            Array.from(subject.options).forEach(function (option) {
                if (!option.value) return;
                option.hidden = subjectByCourse[option.value] !== parseInt(courseId, 10);
            });
        }
    }

    [course, batch].forEach(function (select) {
        if (select) select.addEventListener('change', filterOptions);
    });
    filterOptions();
});
</script>
