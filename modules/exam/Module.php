<?php

namespace app\modules\exam;

use app\modules\course\models\Batch;
use app\modules\course\models\Course;
use app\modules\course\models\Section;
use app\modules\course\models\Subject;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\exam\controllers';
    public $defaultRoute = 'exam/index';

    /** @var int Default pagination size */
    public $pageSize = 20;

    /**
     * Resolves Course, Batch, Section, and Subject dropdown options from the course module.
     *
     * @return array<string, array<int, string>>
     */
    public function resolveAcademicLists(): array
    {
        $lists = [
            'course' => [],
            'batch' => [],
            'section' => [],
            'subject' => [],
        ];

        try {
            if (class_exists(Course::class)) {
                $lists['course'] = ArrayHelper::map(
                    Course::find()->orderBy(['name' => SORT_ASC])->all(),
                    'id',
                    'name'
                );
            }
            if (class_exists(Batch::class)) {
                $lists['batch'] = ArrayHelper::map(
                    Batch::find()->orderBy(['name' => SORT_ASC])->all(),
                    'id',
                    'name'
                );
            }
            if (class_exists(Section::class)) {
                $lists['section'] = ArrayHelper::map(
                    Section::find()->orderBy(['name' => SORT_ASC])->all(),
                    'id',
                    'name'
                );
            }
            if (class_exists(Subject::class)) {
                $lists['subject'] = ArrayHelper::map(
                    Subject::find()->orderBy(['name' => SORT_ASC])->all(),
                    'id',
                    function ($s) {
                        return $s->code ? ($s->name . ' (' . $s->code . ')') : $s->name;
                    }
                );
            }
        } catch (\Throwable $e) {
            // Gracefully fall back to empty lists if tables are not yet migrated
        }

        return $lists;
    }
}
