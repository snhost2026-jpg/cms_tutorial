<?php

use yii\db\Migration;
use app\models\User;

/**
 * Seeds initial RBAC roles, permissions, routes, and default admin user.
 */
class m240101_000003_seed_rbac_and_admin_user extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        if (!$auth) {
            return;
        }

        // 1. Create Roles
        $roles = [
            'SuperAdmin' => 'Super Administrator with unrestricted access',
            'admin' => 'Administrator with full management access',
            'manager' => 'Manager access',
            'employee' => 'Standard employee access',
            'hr' => 'Human Resources access',
        ];

        foreach ($roles as $roleName => $desc) {
            if (!$auth->getRole($roleName)) {
                $role = $auth->createRole($roleName);
                $role->description = $desc;
                $auth->add($role);
            }
        }

        // 2. Create Core Permissions and Routes
        $permissions = [
            '/*' => 'Full application route access',
            '/rights/*' => 'Rights / RBAC management routes',
            '/users/*' => 'Users module routes',
            '/user/*' => 'User CRUD routes',
            '/auth-assignment/*' => 'Role and route assignment routes',
            '/site/*' => 'Public and common site routes',
            'manageRbac' => 'Manage RBAC and user assignments capability',
            'viewOwnProfile' => 'View own profile capability',
        ];

        foreach ($permissions as $permName => $desc) {
            $perm = $auth->getPermission($permName);
            if (!$perm) {
                $perm = $auth->createPermission($permName);
                $perm->description = $desc;
                $auth->add($perm);
            }
        }

        // 3. Assign Permissions to Roles
        $superAdmin = $auth->getRole('SuperAdmin');
        $admin = $auth->getRole('admin');
        $wildcard = $auth->getPermission('/*');
        $manageRbac = $auth->getPermission('manageRbac');

        if ($superAdmin && $wildcard && !$auth->hasChild($superAdmin, $wildcard)) {
            $auth->addChild($superAdmin, $wildcard);
        }
        if ($admin && $wildcard && !$auth->hasChild($admin, $wildcard)) {
            $auth->addChild($admin, $wildcard);
        }
        if ($superAdmin && $manageRbac && !$auth->hasChild($superAdmin, $manageRbac)) {
            $auth->addChild($superAdmin, $manageRbac);
        }
        if ($admin && $manageRbac && !$auth->hasChild($admin, $manageRbac)) {
            $auth->addChild($admin, $manageRbac);
        }

        // 4. Create Default Admin User (if not exists)
        $adminUser = User::findOne(['username' => 'admin']);
        if (!$adminUser) {
            $adminUser = new User();
            $adminUser->username = 'admin';
            $adminUser->email = 'admin@sargnexus.com';
            $adminUser->auth_key = Yii::$app->security->generateRandomString();
            $adminUser->password_hash = Yii::$app->security->generatePasswordHash('Admin@12345');
            $adminUser->status = 10;
            $adminUser->created_at = time();
            $adminUser->updated_at = time();
            $adminUser->save(false);
        }

        // 5. Assign SuperAdmin & admin to admin user
        if ($adminUser) {
            if ($superAdmin && !$auth->getAssignment('SuperAdmin', $adminUser->id)) {
                $auth->assign($superAdmin, $adminUser->id);
            }
            if ($admin && !$auth->getAssignment('admin', $adminUser->id)) {
                $auth->assign($admin, $adminUser->id);
            }
        }
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        if ($auth) {
            $auth->removeAll();
        }

        $adminUser = User::findOne(['username' => 'admin']);
        if ($adminUser) {
            $adminUser->delete();
        }
    }
}
