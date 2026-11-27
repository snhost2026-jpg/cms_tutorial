<?php

// Ensure @mdm/admin alias is always available immediately to Yii's autoloader
Yii::setAlias('@mdm/admin', dirname(__DIR__) . '/vendor/mdmsoft/yii2-admin');

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'cms-tutorial',
    'name' => $params['companyName'],
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Kolkata',

    'as sessionTimeout' => [
        'class' => 'app\components\SessionTimeout',
        'timeout' => 3600,
    ],

    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'site/*',
            'login',
            'logout',
            'debug/*',
            'gii/*',
        ],
    ],

    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@mdm/admin' => '@vendor/mdmsoft/yii2-admin',
    ],
    'modules' => [
        'rights' => [
            'class' => 'mdm\admin\Module',
            'viewPath' => '@app/views/rights',
            'controllerMap' => [
                'assignment' => [
                    'class' => 'mdm\admin\controllers\AssignmentController',
                    'userClassName' => 'app\models\User',
                    'idField' => 'id',
                    'usernameField' => 'username',
                ],
            ],
        ],
        'users' => [
            'class' => 'app\modules\users\Module',
        ],
        'academic' => [
            'class' => 'app\modules\academic\Module',
        ],
        'student' => [
            'class' => 'app\modules\student\StudentModule',
            'createLogin' => true,
            'classLists' => [
                'course' => function () {
                    if (class_exists(\app\modules\academic\models\Course::class)) {
                        try {
                            return \yii\helpers\ArrayHelper::map(\app\modules\academic\models\Course::find()->all(), 'id', 'name');
                        } catch (\Throwable $e) {}
                    }
                    return [];
                },
                'batch' => function () {
                    if (class_exists(\app\modules\academic\models\Batch::class)) {
                        try {
                            return \yii\helpers\ArrayHelper::map(\app\modules\academic\models\Batch::find()->all(), 'id', 'name');
                        } catch (\Throwable $e) {}
                    }
                    return [];
                },
                'section' => function () {
                    if (class_exists(\app\modules\academic\models\Section::class)) {
                        try {
                            return \yii\helpers\ArrayHelper::map(\app\modules\academic\models\Section::find()->all(), 'id', 'name');
                        } catch (\Throwable $e) {}
                    }
                    return [];
                },
            ],
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'KKtK9WeSK8zpfFaIjln5cZXJBOzEHL2p',
            'baseUrl' => '',
            'scriptUrl' => '/index.php',
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'loginUrl' => ['site/login'],
        ],

        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],

        'db' => $db,

        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'timeZone' => 'Asia/Kolkata',
            'defaultTimeZone' => 'Asia/Kolkata',
            'dateFormat' => 'php:M d, Y',
            'datetimeFormat' => 'php:M d, Y h:i:s A',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'rights/<controller:[\w\-]+>/<action:[\w\-]+>' => 'rights/<controller>/<action>',
                'rights/<controller:[\w\-]+>' => 'rights/<controller>/index',
                'rights' => 'rights/assignment/index',
                'attendance' => 'academic/attendance/index',
                'user/<action:[\w\-]+>' => 'users/user/<action>',
                'user' => 'users/user/index',
                'student/<action:[\w\-]+>' => 'student/stu-master/<action>',
                'student' => 'student/stu-master/index',
                'auth-assignment/<action:[\w\-]+>' => 'users/auth-assignment/<action>',
                'auth-assignment' => 'users/auth-assignment/index',
                '' => 'site/login',
                'login' => 'site/login',
                'logout' => 'site/logout',
            ],
        ],
    ],

    'params' => $params,
];

if (YII_ENV_DEV) {
    if (class_exists('yii\debug\Module')) {
        $config['bootstrap'][] = 'debug';
        $config['modules']['debug'] = [
            'class' => 'yii\debug\Module',
        ];
    }
    if (class_exists('yii\gii\Module')) {
        $config['bootstrap'][] = 'gii';
        $config['modules']['gii'] = [
            'class' => 'yii\gii\Module',
        ];
    }
}

return $config;
