<?php

use app\modules\exam\models\ExamQuestion;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $exam app\modules\exam\models\Exam */
/* @var $bankQuestions app\modules\exam\models\ExamQuestion[] */
/* @var $assignedIds int[] */
/* @var $quickQuestion app\modules\exam\models\ExamQuestion */
/* @var $lists array */
/* @var $filters array */

$this->title = 'Select Questions: ' . $exam->title;
$this->params['breadcrumbs'][] = ['label' => 'Online Exams', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $exam->title, 'url' => ['view', 'id' => $exam->id]];
$this->params['breadcrumbs'][] = 'Questions';
?>
<div class="exam-questions-picker">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-list-check text-primary me-2"></i>Assign Questions
            </h3>
            <p class="text-muted small mb-0">
                Exam: <strong><?= Html::encode($exam->title) ?></strong> &bull; Currently selected: <span id="selectedCountBadge" class="badge bg-primary"><?= count($assignedIds) ?></span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" data-bs-toggle="collapse" data-bs-target="#quickAddPanel">
                <i class="fa-solid fa-plus me-1"></i> Quick-Add New Question
            </button>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Exam', ['view', 'id' => $exam->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <!-- Quick-Add New Question Directly Into Exam -->
    <div class="collapse <?= $quickQuestion->hasErrors() ? 'show' : '' ?> mb-4" id="quickAddPanel">
        <div class="card border-0 shadow-sm rounded-4 border-start border-4 border-success">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="fw-bold mb-0 text-success"><i class="fa-solid fa-bolt me-2"></i>Quick-Add Question Directly to This Exam</h5>
            </div>
            <div class="card-body p-4">
                <?php $qForm = ActiveForm::begin(); ?>
                <?= Html::hiddenInput('action_type', 'quick_add') ?>
                <?= $qForm->errorSummary($quickQuestion, ['class' => 'alert alert-danger']) ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?= $qForm->field($quickQuestion, 'course_id')->dropDownList($lists['course'], ['prompt' => '— Any Course —', 'class' => 'form-select']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $qForm->field($quickQuestion, 'subject_id')->dropDownList($lists['subject'], ['prompt' => '— Any Subject —', 'class' => 'form-select']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $qForm->field($quickQuestion, 'marks')->input('number', ['step' => '0.25', 'min' => '0.25', 'class' => 'form-control']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $qForm->field($quickQuestion, 'difficulty')->dropDownList(ExamQuestion::difficultyOptions(), ['class' => 'form-select']) ?>
                    </div>
                </div>

                <?= $qForm->field($quickQuestion, 'question_text')->textarea(['rows' => 2, 'class' => 'form-control', 'placeholder' => 'Enter question text...']) ?>

                <div class="row g-3 mt-1">
                    <div class="col-md-6"><?= $qForm->field($quickQuestion, 'option_a')->textInput(['class' => 'form-control']) ?></div>
                    <div class="col-md-6"><?= $qForm->field($quickQuestion, 'option_b')->textInput(['class' => 'form-control']) ?></div>
                    <div class="col-md-6"><?= $qForm->field($quickQuestion, 'option_c')->textInput(['class' => 'form-control']) ?></div>
                    <div class="col-md-6"><?= $qForm->field($quickQuestion, 'option_d')->textInput(['class' => 'form-control']) ?></div>
                </div>

                <div class="row g-3 mt-2 align-items-end">
                    <div class="col-md-4">
                        <?= $qForm->field($quickQuestion, 'correct_option')->dropDownList(ExamQuestion::optionKeys(), ['prompt' => '— Correct Answer —', 'class' => 'form-select']) ?>
                    </div>
                    <div class="col-md-8 pb-3">
                        <?= Html::submitButton('<i class="fa-solid fa-plus me-1"></i> Create & Attach to Exam', ['class' => 'btn btn-success px-4']) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <!-- Filter Bank -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="get" action="<?= Url::to(['questions', 'id' => $exam->id]) ?>" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Course</label>
                    <?= Html::dropDownList('course_id', $filters['course_id'], $lists['course'], ['prompt' => 'All Courses', 'class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Subject</label>
                    <?= Html::dropDownList('subject_id', $filters['subject_id'], $lists['subject'], ['prompt' => 'All Subjects', 'class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Difficulty</label>
                    <?= Html::dropDownList('difficulty', $filters['difficulty'], ExamQuestion::difficultyOptions(), ['prompt' => 'All', 'class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-filter me-1"></i> Filter Bank</button>
                    <a href="<?= Url::to(['questions', 'id' => $exam->id, 'show_all' => 1]) ?>" class="btn btn-sm btn-outline-secondary">Show All Questions</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Question Selection Form -->
    <?= Html::beginForm(['questions', 'id' => $exam->id], 'post') ?>
    <?= Html::hiddenInput('action_type', 'sync_bank') ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="toggleAllQuestions">
                <label class="form-check-label fw-semibold" for="toggleAllQuestions">Select All Visible (<?= count($bankQuestions) ?>)</label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm px-4">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save Selected Questions
            </button>
        </div>
        <div class="card-body p-0">
            <?php if (empty($bankQuestions)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                    No matching questions in the Question Bank. Use <strong>Quick-Add New Question</strong> above or click <strong>Show All Questions</strong>.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th style="width: 50px;" class="text-center">Pick</th>
                                <th>Question</th>
                                <th style="width: 90px;" class="text-center">Answer</th>
                                <th style="width: 85px;" class="text-center">Marks</th>
                                <th style="width: 100px;" class="text-center">Difficulty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bankQuestions as $q): ?>
                                <?php $checked = in_array((int) $q->id, $assignedIds, true); ?>
                                <tr class="<?= $checked ? 'table-primary bg-opacity-10' : '' ?>">
                                    <td class="text-center">
                                        <input class="form-check-input q-checkbox" type="checkbox" name="question_ids[]" value="<?= $q->id ?>" <?= $checked ? 'checked' : '' ?>>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= Html::encode($q->question_text) ?></div>
                                        <div class="small text-muted mt-1">
                                            <?php if ($q->course): ?><span class="badge bg-light text-dark border me-1"><?= Html::encode($q->course->name) ?></span><?php endif; ?>
                                            <?php if ($q->subject): ?><span class="badge bg-primary-subtle text-primary me-1"><?= Html::encode($q->subject->name) ?></span><?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success"><?= Html::encode($q->correct_option) ?></span></td>
                                    <td class="text-center"><?= rtrim(rtrim(number_format((float) $q->marks, 2), '0'), '.') ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= ucfirst(Html::encode($q->difficulty)) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($bankQuestions)): ?>
            <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Selected Questions
                </button>
            </div>
        <?php endif; ?>
    </div>
    <?= Html::endForm() ?>
</div>

<?php
$js = <<<JS
(function() {
    var toggleAll = document.getElementById('toggleAllQuestions');
    var boxes = document.querySelectorAll('.q-checkbox');
    var badge = document.getElementById('selectedCountBadge');

    function updateCount() {
        var count = document.querySelectorAll('.q-checkbox:checked').length;
        if (badge) badge.textContent = count;
    }

    if (toggleAll) {
        toggleAll.addEventListener('change', function() {
            boxes.forEach(function(cb) { cb.checked = toggleAll.checked; });
            updateCount();
        });
    }
    boxes.forEach(function(cb) {
        cb.addEventListener('change', updateCount);
    });
})();
JS;
$this->registerJs($js);
?>
