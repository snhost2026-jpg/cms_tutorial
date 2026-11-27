<?php

namespace app\modules\student;

/**
 * Student module (prep school edition).
 *
 * Register in config/web.php:
 *
 *   'modules' => [
 *       'student' => [
 *           'class' => 'app\modules\student\StudentModule',
 *           // optional settings, all shown with their defaults:
 *           'registrationPrefix' => 'T',
 *           'pageSize'           => 20,
 *           'createLogin'        => false,
 *           'loginRole'          => 'Student',
 *           'passwordHasher'     => null,
 *           'classLists'         => [],
 *       ],
 *   ],
 */
class StudentModule extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\student\controllers';

    public $defaultRoute = 'stu-master';

    /** Prefix of auto-generated registration numbers, e.g. T202600001 */
    public $registrationPrefix = 'T';

    /** Rows per page in the student list. */
    public $pageSize = 20;

    /**
     * When true, saving a new student also creates a row in app\models\User
     * and an app\models\AuthAssignment (the host application must provide
     * both classes). Off by default so the module works on its own.
     */
    public $createLogin = false;

    /** RBAC item assigned to the created login (only used if createLogin). */
    public $loginRole = 'Student';

    /**
     * Optional callable function ($plainPassword) { return $hash; } used to
     * hash the temporary password of a created login. When null, Yii's
     * security->generatePasswordHash() is used.
     */
    public $passwordHasher = null;

    /**
     * Optional dropdown data for the class-placement fields. Each value is an
     * array [id => label] or a callable returning one. Keys: course, batch,
     * section. Without an entry the field is a plain number input.
     *
     *   'classLists' => [
     *       'course' => function () {
     *           return \yii\helpers\ArrayHelper::map(
     *               \app\modules\course\models\Courses::find()->all(), 'course_id', 'course_name');
     *       },
     *   ],
     */
    public $classLists = [];

    public function init()
    {
        parent::init();
    }
}
