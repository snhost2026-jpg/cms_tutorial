<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $entity */
/** @var array<string, mixed> $definition */
/** @var \app\modules\course\models\CourseRecord $model */
/** @var array{options: array<string, array<int|string, string>>, optionAttributes: array<string, array<int|string, array<string, mixed>>>} $fieldOptions */

$isNewRecord = $model->isNewRecord;
$this->title = ($isNewRecord ? 'Add ' : 'Edit ') . $definition['singular'];
$this->params['breadcrumbs'][] = ['label' => 'Course Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $definition['label'], 'url' => ['records', 'entity' => $entity]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0"><?= Html::encode($this->title) ?></h2>
    <?= Html::a('Back to ' . Html::encode($definition['label']), ['records', 'entity' => $entity], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <?php $form = ActiveForm::begin(); ?>

        <?php foreach ($definition['fields'] as $attribute => $field): ?>
            <?php
            $input = $form->field($model, $attribute);
            switch ($field['type']) {
                case 'textarea':
                    echo $input->textarea(['rows' => 4]);
                    break;
                case 'number':
                    echo $input->input('number', ['step' => $attribute === 'fee_amount' ? '0.01' : '1']);
                    break;
                case 'date':
                    echo $input->input('date');
                    break;
                case 'checkbox':
                    echo $input->checkbox();
                    break;
                case 'select':
                    echo $input->dropDownList($fieldOptions['options'][$attribute] ?? [], [
                        'prompt' => 'Select ' . $model->getAttributeLabel($attribute),
                        'options' => $fieldOptions['optionAttributes'][$attribute] ?? [],
                    ]);
                    break;
                default:
                    echo $input->textInput();
            }
            ?>
        <?php endforeach; ?>

        <div class="d-flex gap-2 mt-4">
            <?= Html::submitButton($isNewRecord ? 'Create' : 'Save Changes', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Cancel', ['records', 'entity' => $entity], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php if ($entity === 'sections'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const courseSelect = document.getElementById('section-course_id');
    const batchSelect = document.getElementById('section-batch_id');

    if (!courseSelect || !batchSelect) {
        return;
    }

    function filterBatches() {
        const selectedCourseId = courseSelect.value;
        let selectedBatchIsValid = false;

        Array.from(batchSelect.options).forEach(function (option) {
            if (option.value === '') {
                option.hidden = false;
                return;
            }

            const matchesCourse = selectedCourseId !== ''
                && option.dataset.courseId === selectedCourseId;
            option.hidden = !matchesCourse;

            if (matchesCourse && option.selected) {
                selectedBatchIsValid = true;
            }
        });

        if (!selectedBatchIsValid) {
            batchSelect.value = '';
        }

        batchSelect.disabled = selectedCourseId === '';
    }

    courseSelect.addEventListener('change', filterBatches);
    filterBatches();
});
</script>
<?php endif; ?>
