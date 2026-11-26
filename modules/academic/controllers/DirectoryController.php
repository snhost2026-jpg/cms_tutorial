<?php

namespace app\modules\academic\controllers;

use Yii;
use app\components\AccessHelper;
use app\modules\academic\models\AcademicRecord;
use app\modules\academic\models\Batch;
use app\modules\academic\models\Course;
use app\modules\academic\models\Section;
use app\modules\academic\models\Subject;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;

class DirectoryController extends Controller
{
    private $_entities = [
        'courses' => [
            'label' => 'Courses',
            'singular' => 'Course',
            'class' => Course::class,
            'fields' => [
                'name' => ['type' => 'text'],
                'code' => ['type' => 'text'],
                'description' => ['type' => 'textarea'],
                'fee_amount' => ['type' => 'number'],
                'active' => ['type' => 'checkbox'],
            ],
        ],
        'batches' => [
            'label' => 'Batches',
            'singular' => 'Batch',
            'class' => Batch::class,
            'fields' => [
                'course_id' => ['type' => 'select', 'relation' => 'course', 'label' => 'Course', 'model' => Course::class],
                'name' => ['type' => 'text'],
                'start_date' => ['type' => 'date'],
                'end_date' => ['type' => 'date'],
                'capacity' => ['type' => 'number'],
            ],
        ],
        'sections' => [
            'label' => 'Sections',
            'singular' => 'Section',
            'class' => Section::class,
            'fields' => [
                'course_id' => ['type' => 'select', 'relation' => 'course', 'label' => 'Course', 'model' => Course::class],
                'batch_id' => [
                    'type' => 'select',
                    'relation' => 'batch',
                    'label' => 'Batch',
                    'model' => Batch::class,
                    'optionData' => ['data-course-id' => 'course_id'],
                ],
                'name' => ['type' => 'text'],
            ],
        ],
        'subjects' => [
            'label' => 'Subjects',
            'singular' => 'Subject',
            'class' => Subject::class,
            'fields' => [
                'course_id' => ['type' => 'select', 'relation' => 'course', 'label' => 'Course', 'model' => Course::class],
                'name' => ['type' => 'text'],
                'code' => ['type' => 'text'],
            ],
        ],
    ];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'records', 'view', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (
            !Yii::$app->user->isGuest
            && !Yii::$app->user->can('SuperAdmin')
            && !Yii::$app->user->can('admin')
            && !Yii::$app->user->can('manageAcademic')
            && !Yii::$app->user->can('manageRbac')
            && !AccessHelper::checkRoute('/' . $action->getUniqueId())
            && !AccessHelper::checkRoute('/academic/*')
            && !AccessHelper::checkRoute('/academic/directory/*')
        ) {
            throw new ForbiddenHttpException('You are not allowed to access academic management.');
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'entities' => $this->_entities,
        ]);
    }

    public function actionRecords($entity)
    {
        $definition = $this->getEntity($entity);
        $modelClass = $definition['class'];
        $relations = [];
        foreach ($definition['fields'] as $field) {
            if (isset($field['relation'])) {
                $relations[] = $field['relation'];
            }
        }

        return $this->render('records', [
            'entity' => $entity,
            'definition' => $definition,
            'dataProvider' => new ActiveDataProvider([
                'query' => $modelClass::find()->with($relations)->orderBy(['id' => SORT_DESC]),
            ]),
        ]);
    }

    public function actionView($entity, $id)
    {
        $definition = $this->getEntity($entity);
        $model = $this->findRecord($definition['class'], $id);

        return $this->render('view', [
            'entity' => $entity,
            'definition' => $definition,
            'model' => $model,
        ]);
    }

    public function actionCreate($entity)
    {
        $definition = $this->getEntity($entity);
        $modelClass = $definition['class'];
        $model = new $modelClass();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', $definition['singular'] . ' created successfully.');
            return $this->redirect(['records', 'entity' => $entity]);
        }

        return $this->render('form', [
            'entity' => $entity,
            'definition' => $definition,
            'model' => $model,
            'fieldOptions' => $this->fieldOptions($definition),
        ]);
    }

    public function actionUpdate($entity, $id)
    {
        $definition = $this->getEntity($entity);
        $model = $this->findRecord($definition['class'], $id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', $definition['singular'] . ' updated successfully.');
            return $this->redirect(['records', 'entity' => $entity]);
        }

        return $this->render('form', [
            'entity' => $entity,
            'definition' => $definition,
            'model' => $model,
            'fieldOptions' => $this->fieldOptions($definition),
        ]);
    }

    public function actionDelete($entity, $id)
    {
        $definition = $this->getEntity($entity);
        $this->findRecord($definition['class'], $id)->delete();
        Yii::$app->session->setFlash('success', $definition['singular'] . ' deleted successfully.');

        return $this->redirect(['records', 'entity' => $entity]);
    }

    private function getEntity($entity)
    {
        if (!isset($this->_entities[$entity])) {
            throw new NotFoundHttpException('Academic section not found.');
        }

        return $this->_entities[$entity];
    }

    /**
     * @param class-string<AcademicRecord> $modelClass
     * @param int|string $id
     */
    private function findRecord($modelClass, $id)
    {
        $model = $modelClass::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Academic record not found.');
        }

        return $model;
    }

    private function fieldOptions(array $definition)
    {
        $options = [];
        $optionAttributes = [];

        foreach ($definition['fields'] as $attribute => $field) {
            if ($field['type'] !== 'select') {
                continue;
            }

            $relatedClass = $field['model'];
            $columns = ['id', 'name'];
            foreach ($field['optionData'] ?? [] as $column) {
                $columns[] = $column;
            }
            $items = $relatedClass::find()
                ->select($columns)
                ->orderBy(['name' => SORT_ASC])
                ->asArray()
                ->all();

            foreach ($items as $item) {
                $options[$attribute][$item['id']] = $item['name'];
                foreach ($field['optionData'] ?? [] as $htmlAttribute => $column) {
                    $optionAttributes[$attribute][$item['id']][$htmlAttribute] = $item[$column];
                }
            }
        }

        return [
            'options' => $options,
            'optionAttributes' => $optionAttributes,
        ];
    }
}
