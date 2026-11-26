<?php

use app\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

AppAsset::register($this);

$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$currentRoute = Yii::$app->controller ? Yii::$app->controller->route : '';
$companyLogo = Yii::$app->params['companyLogo'] ?? '/uploads/logo.png';

// Helper to check route access matching CMS route permissions
if (!function_exists('canAccessRoute')) {
    function canAccessRoute($route) {
        if (Yii::$app->user->isGuest) return false;
        if (Yii::$app->user->can('SuperAdmin') || Yii::$app->user->can('admin')) return true;
        return \app\components\AccessHelper::checkRoute($route);
    }
}

if (!function_exists('isMenuActive')) {
    function isMenuActive($routes = [])
    {
        $currentRoute = Yii::$app->controller ? Yii::$app->controller->route : '';
        foreach ((array)$routes as $route) {
            if ($currentRoute === ltrim($route, '/')) {
                return 'active';
            }
        }
        return '';
    }
}

$canAccessUsersAndRights = !Yii::$app->user->isGuest && (
    Yii::$app->user->can('SuperAdmin') ||
    Yii::$app->user->can('admin') ||
    Yii::$app->user->can('manageRbac') ||
    canAccessRoute('/users/*') ||
    canAccessRoute('/users/user/index') ||
    canAccessRoute('/user/index') ||
    canAccessRoute('/rights/*') ||
    canAccessRoute('/rights/assignment') ||
    canAccessRoute('/rights/role') ||
    canAccessRoute('/rights/permission') ||
    canAccessRoute('/rights/route')
);

$canAccessAcademic = !Yii::$app->user->isGuest && (
    Yii::$app->user->can('SuperAdmin') ||
    Yii::$app->user->can('admin') ||
    Yii::$app->user->can('manageAcademic') ||
    Yii::$app->user->can('manageRbac') ||
    canAccessRoute('/academic/*') ||
    canAccessRoute('/academic/directory/index')
);
?>

<style>
:root {
    --crm-primary: #0d6efd;
    --crm-primary-dark: #0b5ed7;
    --crm-bg: #f4f7fb;
    --crm-card: #ffffff;
    --crm-text: #1f2937;
    --crm-muted: #6b7280;
    --crm-border: #e5e7eb;
    --crm-sidebar: #111827;
    --crm-sidebar-soft: rgba(255,255,255,0.08);
    --crm-sidebar-text: rgba(255,255,255,0.88);
    --crm-sidebar-muted: rgba(255,255,255,0.58);
    --crm-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    --crm-radius: 18px;
    --sidebar-width: 280px;
}

* {
    box-sizing: border-box;
}

html,
body {
    height: 100%;
    margin: 0;
    padding: 0;
}

body.crm-body {
    margin: 0;
    padding: 0;
    background: var(--crm-bg);
    color: var(--crm-text);
    font-family: "Segoe UI", Arial, sans-serif;
    overflow-x: hidden;
}

.crm-shell {
    min-height: 100vh;
    display: flex;
    align-items: stretch;
    background: var(--crm-bg);
    margin: 0;
    padding: 0;
    position: relative;
}

/* SIDEBAR */
.crm-sidebar {
    width: var(--sidebar-width);
    min-width: var(--sidebar-width);
    background: linear-gradient(180deg, #111827 0%, #182235 100%);
    color: var(--crm-sidebar-text);
    padding: 18px 14px;
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    box-shadow: 6px 0 24px rgba(0, 0, 0, 0.08);
    z-index: 1040;
    transition: transform .28s ease;
}

.brand-block {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 10px 18px;
    margin-bottom: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.brand-logo {
    width: 48px;
    height: 48px;
    background: #f4f6f9;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.sidebar-logo {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.brand-title {
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.2;
}

.brand-subtitle {
    color: var(--crm-sidebar-muted);
    font-size: 12px;
    margin-top: 2px;
}

.crm-menu {
    gap: 6px;
    display: flex;
    flex-direction: column;
}

.crm-menu .nav-link,
.crm-menu .nav-item > .nav-link,
.crm-menu .nav-item > a.nav-link {
    color: var(--crm-sidebar-text);
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all .2s ease;
    text-decoration: none;
    position: relative;
}

.crm-menu .nav-link i {
    width: 18px;
    text-align: center;
    font-size: 14px;
}

.crm-menu .nav-link:hover,
.crm-menu .nav-item > a.nav-link:hover {
    background: var(--crm-sidebar-soft);
    color: #fff;
    transform: translateX(2px);
}

.crm-menu .nav-link.active,
.crm-menu .nav-item > a.nav-link.active {
    background: linear-gradient(90deg, rgba(13,110,253,0.95), rgba(59,130,246,0.88));
    color: #fff;
    box-shadow: 0 8px 20px rgba(13,110,253,0.22);
}

.crm-menu .nav-item {
    list-style: none;
    margin: 0;
    padding: 0;
}

/* Submenu in Sidebar */
.crm-menu-dropdown {
    position: relative;
}

.crm-submenu-toggle {
    background: transparent !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    padding: 0 12px !important;
    height: 100%;
    color: var(--crm-sidebar-muted) !important;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: 3;
    border-radius: 0 12px 12px 0;
    transition: color .2s ease;
}

.crm-submenu-toggle:hover {
    color: #fff !important;
}

.crm-submenu-toggle i {
    font-size: 11px;
    transition: transform .25s ease;
}

.crm-submenu-toggle:not(.collapsed) i {
    transform: rotate(180deg);
}

.crm-submenu {
    margin-top: 4px;
    margin-bottom: 6px;
    margin-left: 14px;
    padding-left: 10px;
    border-left: 2px solid rgba(255, 255, 255, 0.12);
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.crm-submenu .nav-link {
    font-size: 13px !important;
    padding: 8px 12px !important;
    border-radius: 8px !important;
    color: rgba(255, 255, 255, 0.72) !important;
    gap: 8px !important;
    font-weight: 400;
}

.crm-submenu .nav-link i {
    font-size: 12px !important;
    width: 16px !important;
    opacity: 0.75;
}

.crm-submenu .nav-link:hover {
    color: #fff !important;
    background: rgba(255, 255, 255, 0.08) !important;
    transform: translateX(2px);
}

.crm-submenu .nav-link.active {
    background: rgba(13, 110, 253, 0.3) !important;
    color: #93c5fd !important;
    font-weight: 600 !important;
    box-shadow: none !important;
}

.crm-submenu .nav-link.active i {
    color: #60a5fa !important;
    opacity: 1 !important;
}

/* MAIN AREA */
.crm-main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    padding: 20px;
    overflow-x: hidden;
    margin: 0;
}

.crm-topbar-wrap {
    width: 100%;
    margin: 0 0 18px 0;
    padding: 0;
}

.crm-topbar {
    width: 100%;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(229,231,235,0.8);
    border-radius: var(--crm-radius);
    box-shadow: var(--crm-shadow);
    padding: 14px 18px;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: nowrap;
}

.crm-topbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
}

.crm-topbar-logo {
    height: 40px;
    width: auto;
    object-fit: contain;
    flex-shrink: 0;
}

.crm-topbar-title {
    font-weight: 700;
    font-size: 17px;
    color: #111827;
    line-height: 1.2;
}

.crm-topbar-subtitle {
    font-size: 12px;
    color: var(--crm-muted);
}

.crm-user-toggle {
    border: 1px solid var(--crm-border);
    background: #fff;
    border-radius: 12px;
    padding: 8px 14px;
    color: #111827;
    font-weight: 600;
    min-height: 42px;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.crm-user-toggle:hover {
    background: #f8fafc;
    color: var(--crm-primary);
}

.crm-page-head {
    width: 100%;
    margin: 14px 0 0 0;
    padding: 0 4px;
}

.page-heading {
    margin: 0;
    font-size: 26px;
    font-weight: 700;
    color: #111827;
}

.page-subheading {
    margin-top: 4px;
    font-size: 14px;
    color: var(--crm-muted);
}

.crm-content {
    width: 100%;
    margin-top: 18px;
    padding: 0;
    flex: 1;
}

.crm-alert {
    border: 0;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    margin-bottom: 16px;
}

/* MOBILE TOGGLE */
.crm-mobile-toggle {
    width: 42px;
    height: 42px;
    border: 1px solid var(--crm-border);
    background: #fff;
    border-radius: 12px;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #111827;
    box-shadow: 0 6px 16px rgba(0,0,0,0.05);
    flex-shrink: 0;
    padding: 0;
}

.crm-sidebar-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 1035;
    opacity: 0;
    visibility: hidden;
    transition: all .25s ease;
}

/* MOBILE RESPONSIVENESS */
@media (max-width: 991.98px) {
    html, body {
        overflow-x: hidden !important;
    }

    .crm-shell {
        display: block !important;
    }

    .crm-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        bottom: 0 !important;
        height: 100vh !important;
        width: var(--sidebar-width) !important;
        max-width: 82vw !important;
        min-width: unset !important;
        transform: translateX(-100%) !important;
        z-index: 1055 !important;
    }

    body.sidebar-open .crm-sidebar {
        transform: translateX(0) !important;
    }

    .crm-sidebar-backdrop {
        z-index: 1050 !important;
    }

    body.sidebar-open .crm-sidebar-backdrop {
        opacity: 1;
        visibility: visible;
    }

    .crm-main {
        width: 100% !important;
        padding: 12px !important;
    }

    .crm-mobile-toggle {
        display: inline-flex !important;
    }
}
</style>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title ?: Yii::$app->name) ?></title>

    <link rel="icon" type="image/png" href="<?= Html::encode($companyLogo) ?>" onerror="this.href='/favicon.ico'">

    <?php $this->head() ?>
</head>
<body class="crm-body">
<?php $this->beginBody() ?>

<div class="crm-sidebar-backdrop" id="crmSidebarBackdrop"></div>

<div class="crm-shell">
    <!-- SIDEBAR -->
    <aside class="crm-sidebar" id="crmSidebar">
        <div class="brand-block">
            <div class="brand-logo">
                <img src="<?= Html::encode($companyLogo) ?>" alt="<?= Html::encode(Yii::$app->name) ?> logo" class="sidebar-logo" onerror="this.style.display='none'">
            </div>
            <div>
                <div class="brand-title"><?= Html::encode(Yii::$app->name) ?></div>
                <div class="brand-subtitle">Enterprise Workspace</div>
            </div>
        </div>

        <nav class="nav flex-column crm-menu">
            <a class="nav-link <?= isMenuActive(['/site/index', '']) ?>" href="<?= Url::to(['/site/index']) ?>">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>

            <?php if ($canAccessAcademic): ?>
                <?php
                $isAcademicActive = strpos($currentRoute, 'academic/') === 0;
                $currentAcademicEntity = Yii::$app->request->get('entity', '');
                ?>
                <div class="nav-item crm-menu-dropdown">
                    <div class="d-flex align-items-center position-relative">
                        <a class="nav-link flex-grow-1 <?= $isAcademicActive ? 'active' : '' ?>" href="<?= Url::to(['/academic']) ?>" style="padding-right: 38px;">
                            <i class="fa-solid fa-graduation-cap"></i> Academic Management
                        </a>
                        <button type="button" class="btn btn-link crm-submenu-toggle <?= $isAcademicActive ? '' : 'collapsed' ?>"
                                data-bs-toggle="collapse" data-bs-target="#academicSubmenu"
                                aria-expanded="<?= $isAcademicActive ? 'true' : 'false' ?>" aria-label="Toggle Academic submenu">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="collapse <?= $isAcademicActive ? 'show' : '' ?> crm-submenu" id="academicSubmenu">
                        <a class="nav-link <?= $currentRoute === 'academic/directory/index' ? 'active' : '' ?>" href="<?= Url::to(['/academic']) ?>">
                            <i class="fa-solid fa-house"></i> Overview
                        </a>
                        <a class="nav-link <?= $currentAcademicEntity === 'courses' ? 'active' : '' ?>" href="<?= Url::to(['/academic/directory/records', 'entity' => 'courses']) ?>">
                            <i class="fa-solid fa-book-open"></i> Courses
                        </a>
                        <a class="nav-link <?= $currentAcademicEntity === 'subjects' ? 'active' : '' ?>" href="<?= Url::to(['/academic/directory/records', 'entity' => 'subjects']) ?>">
                            <i class="fa-solid fa-book"></i> Subjects
                        </a>
                        <a class="nav-link <?= $currentAcademicEntity === 'batches' ? 'active' : '' ?>" href="<?= Url::to(['/academic/directory/records', 'entity' => 'batches']) ?>">
                            <i class="fa-solid fa-layer-group"></i> Batches
                        </a>
                        <a class="nav-link <?= $currentAcademicEntity === 'sections' ? 'active' : '' ?>" href="<?= Url::to(['/academic/directory/records', 'entity' => 'sections']) ?>">
                            <i class="fa-solid fa-list"></i> Sections
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($canAccessUsersAndRights): ?>
                <?php $isUsersActive = strpos($currentRoute, 'users/') === 0 || strpos($currentRoute, 'user/') === 0 || strpos($currentRoute, 'rights') === 0; ?>
                <div class="nav-item crm-menu-dropdown">
                    <div class="d-flex align-items-center position-relative">
                        <a class="nav-link flex-grow-1 <?= $isUsersActive ? 'active' : '' ?>" href="<?= Url::to(['/users/user/index']) ?>" style="padding-right: 38px;">
                            <i class="fa-solid fa-user-gear"></i> Users &amp; Access
                        </a>
                        <button type="button" class="btn btn-link crm-submenu-toggle <?= $isUsersActive ? '' : 'collapsed' ?>" 
                                data-bs-toggle="collapse" data-bs-target="#usersSubmenu" 
                                aria-expanded="<?= $isUsersActive ? 'true' : 'false' ?>" aria-label="Toggle Users submenu">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="collapse <?= $isUsersActive ? 'show' : '' ?> crm-submenu" id="usersSubmenu">
                        <a class="nav-link <?= isMenuActive(['/users/user/index', '/users/user/update', '/user/index', '/user/update']) ?>" href="<?= Url::to(['/users/user/index']) ?>">
                            <i class="fa-solid fa-users"></i> All Users
                        </a>
                        <a class="nav-link <?= isMenuActive(['/users/user/create', '/user/create']) ?>" href="<?= Url::to(['/users/user/create']) ?>">
                            <i class="fa-solid fa-user-plus"></i> Add User
                        </a>
                        <a class="nav-link <?= isMenuActive(['/users/user/login-history', '/user/login-history']) ?>" href="<?= Url::to(['/users/user/login-history']) ?>">
                            <i class="fa-solid fa-clock-rotate-left"></i> Login History
                        </a>
                        <?php if (Yii::$app->user->can('SuperAdmin') || Yii::$app->user->can('admin') || canAccessRoute('/rights/*') || canAccessRoute('/rights/assignment')): ?>
                            <a class="nav-link <?= isMenuActive(['/rights/assignment/index', '/rights/assignment/view', '/rights/assignment']) ?>" href="<?= Url::to(['/rights/assignment']) ?>">
                                <i class="fa-solid fa-user-shield"></i> Assignments
                            </a>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('SuperAdmin') || Yii::$app->user->can('admin') || canAccessRoute('/rights/*') || canAccessRoute('/rights/role')): ?>
                            <a class="nav-link <?= isMenuActive(['/rights/role/index', '/rights/role/view', '/rights/role/create', '/rights/role/update', '/rights/role']) ?>" href="<?= Url::to(['/rights/role']) ?>">
                                <i class="fa-solid fa-shield-halved"></i> Roles
                            </a>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('SuperAdmin') || Yii::$app->user->can('admin') || canAccessRoute('/rights/*') || canAccessRoute('/rights/permission')): ?>
                            <a class="nav-link <?= isMenuActive(['/rights/permission/index', '/rights/permission/view', '/rights/permission/create', '/rights/permission/update', '/rights/permission']) ?>" href="<?= Url::to(['/rights/permission']) ?>">
                                <i class="fa-solid fa-key"></i> Permissions
                            </a>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('SuperAdmin') || Yii::$app->user->can('admin') || canAccessRoute('/rights/*') || canAccessRoute('/rights/route')): ?>
                            <a class="nav-link <?= isMenuActive(['/rights/route/index', '/rights/route/create', '/rights/route']) ?>" href="<?= Url::to(['/rights/route']) ?>">
                                <i class="fa-solid fa-route"></i> Routes
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <a class="nav-link <?= isMenuActive(['/site/about']) ?>" href="<?= Url::to(['/site/about']) ?>">
                <i class="fa-solid fa-circle-info"></i> About
            </a>

            <a class="nav-link <?= isMenuActive(['/site/contact']) ?>" href="<?= Url::to(['/site/contact']) ?>">
                <i class="fa-solid fa-envelope"></i> Contact
            </a>

            <?php if (!Yii::$app->user->isGuest): ?>
                <a class="nav-link text-danger mt-3" href="<?= Url::to(['/site/logout']) ?>" data-method="post">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            <?php else: ?>
                <a class="nav-link mt-3" href="<?= Url::to(['/site/login']) ?>">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
            <?php endif; ?>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="crm-main">
        <div class="crm-topbar-wrap">
            <header class="crm-topbar">
                <div class="d-flex align-items-center gap-2">
                    <button class="crm-mobile-toggle" type="button" id="crmSidebarToggle" aria-label="Toggle navigation">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <div class="crm-topbar-left">
                        <img src="<?= Html::encode($companyLogo) ?>" alt="<?= Html::encode(Yii::$app->name) ?> logo" class="crm-topbar-logo" onerror="this.style.display='none'">
                        <div style="min-width:0;">
                            <div class="crm-topbar-title"><?= Html::encode(Yii::$app->name) ?></div>
                            <div class="crm-topbar-subtitle">Enterprise Workspace</div>
                        </div>
                    </div>
                </div>

                <div class="dropdown crm-user-area flex-shrink-0">
                    <?php if (!Yii::$app->user->isGuest): ?>
                        <a href="#" class="dropdown-toggle crm-user-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-circle-user text-primary fs-5"></i>
                            <span><?= Html::encode(Yii::$app->user->identity->username ?? 'Account') ?></span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                            <li>
                                <div class="px-3 py-1 text-muted small">
                                    Signed in as <strong><?= Html::encode(Yii::$app->user->identity->username) ?></strong>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <?= Html::a('<i class="fa-solid fa-key me-2"></i> Change Password', ['/site/change-password'], ['class' => 'dropdown-item']) ?>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <?= Html::a('<i class="fa-solid fa-right-from-bracket me-2 text-danger"></i> Logout', ['/site/logout'], [
                                    'data-method' => 'post',
                                    'class' => 'dropdown-item text-danger'
                                ]) ?>
                            </li>
                        </ul>
                    <?php else: ?>
                        <?= Html::a('<i class="fa-solid fa-right-to-bracket me-1"></i> Login', ['/site/login'], ['class' => 'btn btn-primary btn-sm rounded-pill px-3']) ?>
                    <?php endif; ?>
                </div>
            </header>

            <div class="crm-page-head">
                <h1 class="page-heading"><?= Html::encode($this->title ?: 'Dashboard') ?></h1>
                <?php if (!empty($this->params['breadcrumbs'])): ?>
                    <div class="page-subheading">
                        <?= Breadcrumbs::widget([
                            'links' => $this->params['breadcrumbs'],
                            'options' => ['class' => 'breadcrumb mb-0 mt-1', 'style' => 'font-size: 13px;']
                        ]) ?>
                    </div>
                <?php else: ?>
                    <div class="page-subheading">Enterprise management and collaboration workspace</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="crm-content container-fluid px-0">
            <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
                <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show crm-alert" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>

            <?= $content ?>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const sidebarToggle = document.getElementById('crmSidebarToggle');
    const sidebarBackdrop = document.getElementById('crmSidebarBackdrop');
    const sidebar = document.getElementById('crmSidebar');

    function closeSidebar() {
        body.classList.remove('sidebar-open');
    }

    function openSidebar() {
        body.classList.add('sidebar-open');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            if (body.classList.contains('sidebar-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    if (sidebar) {
        sidebar.querySelectorAll('a.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            });
        });
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    });
});
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
