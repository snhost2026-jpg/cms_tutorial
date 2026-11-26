<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;

$canAccessUsersAndRights = !Yii::$app->user->isGuest && (
    Yii::$app->user->can('SuperAdmin') ||
    Yii::$app->user->can('admin') ||
    Yii::$app->user->can('manageRbac') ||
    \app\components\AccessHelper::checkRoute('/rights/*') ||
    \app\components\AccessHelper::checkRoute('/users/*')
);

$canAccessAcademic = !Yii::$app->user->isGuest && (
    Yii::$app->user->can('SuperAdmin') ||
    Yii::$app->user->can('admin') ||
    Yii::$app->user->can('manageAcademic') ||
    Yii::$app->user->can('manageRbac') ||
    \app\components\AccessHelper::checkRoute('/academic/*') ||
    \app\components\AccessHelper::checkRoute('/academic/directory/index')
);

$items = [
    [
        'label' => 'Home',
        'url' => ['/site/index'],
    ],
    [
        'label' => 'Academic',
        'url' => ['/academic'],
        'visible' => $canAccessAcademic,
    ],
    [
        'label' => 'Users',
        'url' => ['/users/user/index'],
        'visible' => $canAccessUsersAndRights,
    ],
    [
        'label' => 'Rights & RBAC',
        'visible' => $canAccessUsersAndRights,
        'items' => [
            ['label' => 'Role Assignments', 'url' => ['/rights/assignment']],
            ['label' => 'Roles', 'url' => ['/rights/role']],
            ['label' => 'Permissions', 'url' => ['/rights/permission']],
            ['label' => 'Routes', 'url' => ['/rights/route']],
        ],
    ],
    [
        'label' => 'About',
        'url' => ['/site/about'],
    ],
    [
        'label' => 'Contact',
        'url' => ['/site/contact'],
    ],
    [
        'label' => 'Login',
        'url' => ['/site/login'],
        'visible' => Yii::$app->user->isGuest,
    ],
    [
        'label' => Html::encode(Yii::$app->user->identity?->username ?? 'Account'),
        'visible' => !Yii::$app->user->isGuest,
        'items' => [
            [
                'label' => '<i class="fa-solid fa-key me-2"></i> Change Password',
                'url' => ['/site/change-password'],
            ],
            '<div class="dropdown-divider"></div>',
            [
                'label' => '<i class="fa-solid fa-right-from-bracket me-2 text-danger"></i> Logout',
                'url' => ['/site/logout'],
                'linkOptions' => [
                    'data-method' => 'post',
                    'class' => 'text-danger',
                ],
            ],
        ],
    ],
];

?>
<header id="header">
    <?php NavBar::begin(
        [
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
        ],
    ) ?>
    <?= Nav::widget(
        [
            'options' => ['class' => 'navbar-nav me-auto'],
            'encodeLabels' => false,
            'items' => $items,
        ],
    ) ?>
    <?= Html::button(
        '&#127769;',
        [
            'id' => 'theme-toggle',
            'class' => 'btn btn-link nav-link fs-5',
            'aria-label' => 'Switch to dark mode',
        ],
    ) ?>
    <?php NavBar::end() ?>
</header>
