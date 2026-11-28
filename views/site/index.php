<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$this->params['breadcrumbs'] = [];
?>

<div class="site-index">
    <!-- Welcome Banner -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); border-radius: 16px; color: white;">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-2">Welcome to <?= Html::encode(Yii::$app->name) ?></h2>
                    <p class="mb-0 opacity-75" style="font-size: 15px;">
                        Enterprise administration portal with Role-Based Access Control (RBAC), user management, and secure authentication.
                    </p>
                </div>
                <div>
                    <span class="badge bg-white text-primary px-3 py-2 fs-6 rounded-pill">
                        <i class="fa-solid fa-user-shield me-1"></i>
                        <?= Yii::$app->user->isGuest ? 'Guest' : Html::encode(Yii::$app->user->identity->username) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access Dashboard Cards -->
    <div class="row g-4 mb-4">
        <!-- Student Management Card -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div style="width: 48px; height: 48px; background: rgba(13, 110, 253, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-user-graduate text-primary fs-5"></i>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">Students</span>
                    </div>
                    <h5 class="fw-bold mb-1">Student Management</h5>
                    <p class="text-muted small mb-3">Manage admissions, student info, class placements, and trash.</p>
                    <a href="<?= Url::to(['/student/stu-master/index']) ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                        View Students <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Course Management Card -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div style="width: 48px; height: 48px; background: rgba(25, 135, 84, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-graduation-cap text-success fs-5"></i>
                        </div>
                        <span class="badge bg-success-subtle text-success">Courses</span>
                    </div>
                    <h5 class="fw-bold mb-1">Course Management</h5>
                    <p class="text-muted small mb-3">Configure courses, subjects, academic batches, and sections.</p>
                    <a href="<?= Url::to(['/course']) ?>" class="btn btn-outline-success btn-sm rounded-pill w-100">
                        View Courses <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Weekly Timetable Card -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div style="width: 48px; height: 48px; background: rgba(13, 110, 253, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-calendar-days text-primary fs-5"></i>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">Schedule</span>
                    </div>
                    <h5 class="fw-bold mb-1">Weekly Timetable</h5>
                    <p class="text-muted small mb-3">Create, update, and view weekly class schedules for batches and faculty.</p>
                    <a href="<?= Url::to(['/timetable/timetable/index']) ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                        Open Timetable <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Employee Management Card -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div style="width: 48px; height: 48px; background: rgba(13, 110, 253, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-user-tie text-primary fs-5"></i>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">Employees</span>
                    </div>
                    <h5 class="fw-bold mb-1">Employee Management</h5>
                    <p class="text-muted small mb-3">Manage employee profiles and teaching classifications.</p>
                    <a href="<?= Url::to(['/employee']) ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                        View Employees <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div style="width: 48px; height: 48px; background: rgba(111, 66, 193, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-users text-purple fs-5" style="color: #6f42c1;"></i>
                        </div>
                        <span class="badge bg-light text-dark">Staff & Users</span>
                    </div>
                    <h5 class="fw-bold mb-1">User Management</h5>
                    <p class="text-muted small mb-3">Create users, manage credentials, and view login activity.</p>
                    <a href="<?= Url::to(['/users/user/index']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill w-100">
                        View Users <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Role Assignments Card -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div style="width: 48px; height: 48px; background: rgba(255, 193, 7, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-user-shield text-warning fs-5"></i>
                        </div>
                        <span class="badge bg-warning-subtle text-warning">RBAC</span>
                    </div>
                    <h5 class="fw-bold mb-1">Role Assignments</h5>
                    <p class="text-muted small mb-3">Assign roles and route permissions to users via dual listbox.</p>
                    <a href="<?= Url::to(['/rights/assignment']) ?>" class="btn btn-outline-warning btn-sm rounded-pill w-100">
                        Manage Rights <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Roles & Permissions Card -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div style="width: 48px; height: 48px; background: rgba(220, 53, 69, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-shield-halved text-danger fs-5"></i>
                        </div>
                        <span class="badge bg-danger-subtle text-danger">Security</span>
                    </div>
                    <h5 class="fw-bold mb-1">Roles & Permissions</h5>
                    <p class="text-muted small mb-3">Define granular roles, permissions, and rule trees.</p>
                    <a href="<?= Url::to(['/rights/role']) ?>" class="btn btn-outline-danger btn-sm rounded-pill w-100">
                        View Roles <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Route Access Card -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div style="width: 48px; height: 48px; background: rgba(13, 202, 240, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-route text-info fs-5"></i>
                        </div>
                        <span class="badge bg-info-subtle text-info">Routing</span>
                    </div>
                    <h5 class="fw-bold mb-1">Route Discovery</h5>
                    <p class="text-muted small mb-3">Automatically scan and register application routes.</p>
                    <a href="<?= Url::to(['/rights/route']) ?>" class="btn btn-outline-info btn-sm rounded-pill w-100">
                        Discover Routes <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- System Info Panel -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-circle-info text-primary me-2"></i>Quick Navigation Guide</h5>
        </div>
        <div class="card-body px-4 pb-4 pt-1">
            <p class="text-muted mb-3">
                Use the sidebar on the left to navigate across modules. On mobile or smaller screens, click the hamburger menu icon <i class="fa-solid fa-bars mx-1"></i> in the top bar to toggle the sidebar drawer.
            </p>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3">
                        <div class="fw-semibold mb-1"><i class="fa-solid fa-users text-primary me-1"></i> All Users</div>
                        <div class="text-muted small">Access user records, reset passwords, and toggle active status.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3">
                        <div class="fw-semibold mb-1"><i class="fa-solid fa-clock-rotate-left text-success me-1"></i> Login History</div>
                        <div class="text-muted small">Audit user sessions, login times, IP addresses, and user agents.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3">
                        <div class="fw-semibold mb-1"><i class="fa-solid fa-shield-halved text-warning me-1"></i> Rights & RBAC</div>
                        <div class="text-muted small">Manage hierarchical roles, permissions, and routes.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
