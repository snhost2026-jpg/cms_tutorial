<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\web\ForbiddenHttpException;

/**
 * AccessHelper provides route/page-based access checking and route discovery
 * matching the CMS pattern (routes stored as permissions like '/module/controller/*', '/controller/action').
 */
class AccessHelper extends Component
{
    private static $_userRoutes = [];

    /**
     * Check if a user has access to a specific route or action.
     * Checks:
     * 1. Direct permission to exact route (e.g. '/sales/lead/create')
     * 2. Wildcard controller permission (e.g. '/sales/lead/*')
     * 3. Wildcard module permission (e.g. '/sales/*')
     * 4. Superadmin full wildcard ('/*')
     * 5. Also checks Yii::$app->user->can() for traditional RBAC roles and permissions
     *
     * @param string $route
     * @param int|null $userId
     * @return bool
     */
    public static function checkRoute($route, $userId = null)
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        if ($userId === null) {
            $userId = Yii::$app->user->id;
            $user = Yii::$app->user;
        } else {
            $user = $userId;
        }

        // If the user has SuperAdmin role or '/*', they have access to everything
        if (Yii::$app->user->can('SuperAdmin') || Yii::$app->user->can('admin') || Yii::$app->authManager->checkAccess($userId, '/*')) {
            return true;
        }

        // Use standard mdmsoft/yii2-admin helper if available
        if (class_exists(\mdm\admin\components\Helper::class)) {
            try {
                if (\mdm\admin\components\Helper::checkRoute($route, [], $user)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Fall back to direct route check below
            }
        }

        // Normalize route to start with '/'
        $route = '/' . ltrim($route, '/');

        $assignedRoutes = static::getAssignedRoutesByUser($userId);

        // 1. Direct match
        if (isset($assignedRoutes[$route])) {
            return true;
        }

        // 2. Trailing wildcard matching (e.g. /sales/lead/* or /sales/*)
        $r = $route;
        while (($pos = strrpos($r, '/')) > 0) {
            $r = substr($r, 0, $pos);
            if (isset($assignedRoutes[$r . '/*'])) {
                return true;
            }
        }

        if (isset($assignedRoutes['/*'])) {
            return true;
        }

        return false;
    }

    /**
     * Get all assigned route items for a user (including inherited from roles via auth_item_child)
     * @param int $userId
     * @return array
     */
    public static function getAssignedRoutesByUser($userId)
    {
        if (!isset(self::$_userRoutes[$userId])) {
            $routes = [];
            $auth = Yii::$app->authManager;

            // 1. Permissions/routes assigned directly via authManager
            try {
                $permissions = $auth->getPermissionsByUser($userId);
                foreach ($permissions as $item) {
                    if ($item->name !== '' && $item->name[0] === '/') {
                        $routes[$item->name] = true;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore if authManager table setup is partial
            }

            // 2. Direct items assigned to user in auth_assignment
            $directItems = (new \yii\db\Query())
                ->select(['item_name'])
                ->from('auth_assignment')
                ->where(['user_id' => (string)$userId])
                ->column();

            $parentRoles = [];
            foreach ($directItems as $name) {
                if ($name !== '' && $name[0] === '/') {
                    $routes[$name] = true;
                } else {
                    $parentRoles[] = $name;
                }
            }

            // 3. Resolve all child routes assigned to these roles in auth_item_child recursively
            if (!empty($parentRoles)) {
                $childItems = static::getChildrenRecursive($parentRoles);
                foreach ($childItems as $childName) {
                    if ($childName !== '' && $childName[0] === '/') {
                        $routes[$childName] = true;
                    }
                }
            }

            self::$_userRoutes[$userId] = $routes;
        }

        return self::$_userRoutes[$userId];
    }

    /**
     * Recursively retrieves all children (permissions and routes) for given parent item names
     * from auth_item_child.
     * @param array $parents
     * @return array
     */
    public static function getChildrenRecursive(array $parents)
    {
        $allChildren = [];
        $queue = array_values(array_unique($parents));
        $visited = [];

        while (!empty($queue)) {
            $current = array_shift($queue);
            if (isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;

            $children = (new \yii\db\Query())
                ->select(['child'])
                ->from('auth_item_child')
                ->where(['parent' => $current])
                ->column();

            foreach ($children as $child) {
                $allChildren[$child] = true;
                if (!isset($visited[$child])) {
                    $queue[] = $child;
                }
            }
        }

        return array_keys($allChildren);
    }

    /**
     * Scans and returns all available controller routes in the CRM,
     * categorized by module / feature.
     * @return array
     */
    public static function getSystemRoutes()
    {
        return [
            'Global / Full Access' => [
                '/*' => 'Full System Access (/*)',
            ],
            'Dashboard & Core' => [
                '/site/*' => 'All Site Pages (/site/*)',
                '/site/index' => 'Dashboard (/site/index)',
                '/site/change-password' => 'Change Password (/site/change-password)',
            ],
            'User & RBAC Management' => [
                '/users/*' => 'All Users Module Actions (/users/*)',
                '/users/user/*' => 'All User Management Actions (/users/user/*)',
                '/users/user/index' => 'User List (/users/user/index)',
                '/users/user/create' => 'Create User (/users/user/create)',
                '/users/user/update' => 'Edit User (/users/user/update)',
                '/users/user/check-username' => 'Check Username Availability (/users/user/check-username)',
                '/users/user/reset-password' => 'Reset Password (/users/user/reset-password)',
                '/users/user/toggle-status' => 'Activate/Deactivate User (/users/user/toggle-status)',
                '/users/user/login-history' => 'Login History (/users/user/login-history)',
                '/users/auth-assignment/*' => 'All Auth Assignment (/users/auth-assignment/*)',
                '/users/auth-assignment/index' => 'Auth Assignment List (/users/auth-assignment/index)',
                '/users/auth-assignment/user' => 'User Route Assignments (/users/auth-assignment/user)',
                '/users/auth-assignment/roles' => 'Role Access List (/users/auth-assignment/roles)',
                '/users/auth-assignment/role' => 'Role Route Assignments (/users/auth-assignment/role)',
                '/users/auth-assignment/create' => 'Create Auth Assignment (/users/auth-assignment/create)',
                '/users/auth-assignment/delete' => 'Delete Auth Assignment (/users/auth-assignment/delete)',
                '/users/auth-assignment/view' => 'View Auth Assignment (/users/auth-assignment/view)',
                '/user/*' => 'Legacy User Actions (/user/*)',
                '/user/index' => 'Legacy User List (/user/index)',
                '/user/create' => 'Legacy Create User (/user/create)',
                '/user/reset-password' => 'Legacy Reset Password (/user/reset-password)',
                '/user/toggle-status' => 'Legacy Activate/Deactivate User (/user/toggle-status)',
                '/user/login-history' => 'Legacy Login History (/user/login-history)',
                '/auth-assignment/*' => 'Legacy Auth Assignment (/auth-assignment/*)',
                '/auth-assignment/index' => 'Legacy Auth Assignment List (/auth-assignment/index)',
                '/auth-assignment/create' => 'Legacy Create Auth Assignment (/auth-assignment/create)',
                '/auth-assignment/update' => 'Legacy Update Auth Assignment (/auth-assignment/update)',
                '/auth-assignment/delete' => 'Legacy Delete Auth Assignment (/auth-assignment/delete)',
                '/auth-assignment/view' => 'Legacy View Auth Assignment (/auth-assignment/view)',
            ],
            'Employee & Profile' => [
                '/employee/*' => 'All Employee Actions (/employee/*)',
                '/employee/my-profile' => 'My Profile (/employee/my-profile)',
                '/employee/index' => 'Employees List (/employee/index)',
                '/employee/create' => 'Add Employee (/employee/create)',
                '/employee/view' => 'View Employee (/employee/view)',
                '/employee/update' => 'Edit Employee (/employee/update)',
                '/employee/delete' => 'Delete Employee (/employee/delete)',
            ],
            'Observations' => [
                '/observation/*' => 'All Observation Pages (/observation/*)',
                '/observation/default/index' => 'Observations List (/observation/default/index)',
                '/observation/default/create' => 'New Observation (/observation/default/create)',
                '/observation/default/view' => 'View Observation (/observation/default/view)',
            ],
            'Financial Records Module' => [
                '/finance/*' => 'All Financial Module Pages (/finance/*)',
                '/finance/record/*' => 'All Financial Records (/finance/record/*)',
                '/finance/record/index' => 'Records Register (/finance/record/index)',
                '/finance/record/create' => 'New Financial Record (/finance/record/create)',
                '/finance/record/view' => 'View Financial Record (/finance/record/view)',
                '/finance/record/approve' => 'Approve/Reject Record (/finance/record/approve)',
                '/finance/head/*' => 'All Finance Heads (/finance/head/*)',
                '/finance/head/index' => 'Finance Classification Heads (/finance/head/index)',
            ],
            'Sales Module - Core & Workflow' => [
                '/sales/*' => 'All Sales Module Pages (/sales/*)',
                '/sales/dashboard/index' => 'Sales Dashboard (/sales/dashboard/index)',
                '/sales/agenda/index' => 'Action Center (/sales/agenda/index)',
                '/sales/lead/*' => 'All Lead Actions (/sales/lead/*)',
                '/sales/lead/index' => 'Leads List (/sales/lead/index)',
                '/sales/lead/create' => 'Create Lead (/sales/lead/create)',
                '/sales/lead/view' => 'View Lead Profile (/sales/lead/view)',
                '/sales/activity/*' => 'All Lead Activities (/sales/activity/*)',
                '/sales/activity/lead' => 'Log Lead Activity (/sales/activity/lead)',
                '/sales/import/index' => 'Upload CSV Leads (/sales/import/index)',
                '/sales/excel-import/index' => 'Upload Excel Leads (/sales/excel-import/index)',
            ],
            'Sales Module - Management & Commercial' => [
                '/sales/assignment/index' => 'BDE Lead Assignment (/sales/assignment/index)',
                '/sales/requirement/*' => 'Requirements Management (/sales/requirement/*)',
                '/sales/requirement/index' => 'Requirements List (/sales/requirement/index)',
                '/sales/requirement/create' => 'Create Requirement (/sales/requirement/create)',
                '/sales/quotation/*' => 'Quotations Management (/sales/quotation/*)',
                '/sales/quotation/index' => 'Quotations List (/sales/quotation/index)',
                '/sales/quotation/create' => 'Create Quotation (/sales/quotation/create)',
                '/sales/order/*' => 'Sales Orders Management (/sales/order/*)',
                '/sales/order/index' => 'Orders List (/sales/order/index)',
                '/sales/order/view' => 'Order View & Payments (/sales/order/view)',
                '/sales/handover/*' => 'Project Handovers (/sales/handover/*)',
                '/sales/handover/index' => 'Handovers List (/sales/handover/index)',
                '/sales/invoice/*' => 'Invoices & Billing (/sales/invoice/*)',
                '/sales/invoice/index' => 'Invoices List (/sales/invoice/index)',
                '/sales/invoice/create' => 'Create Tax Invoice (/sales/invoice/create)',
                '/sales/subscription/*' => 'Subscriptions Management (/sales/subscription/*)',
                '/sales/subscription/index' => 'Subscriptions List (/sales/subscription/index)',
                '/sales/report/collections' => 'Collections & Audit Report (/sales/report/collections)',
                '/sales/performance/index' => 'BDE Performance Leaderboard (/sales/performance/index)',
                '/sales/head/index' => 'Sales Classification Heads (/sales/head/index)',
            ],
            'Tasks & Projects' => [
                '/project/*' => 'All Projects (/project/*)',
                '/project/index' => 'Projects List (/project/index)',
                '/employee-task/*' => 'Task Management (/employee-task/*)',
                '/employee-task/index' => 'Task Assignment List (/employee-task/index)',
                '/employee-task/my-tasks' => 'My Tasks (/employee-task/my-tasks)',
                '/employee-task-daily-log/*' => 'Daily Logs Management (/employee-task-daily-log/*)',
                '/employee-task-daily-log/my-logs' => 'My Daily Logs (/employee-task-daily-log/my-logs)',
                '/employee-task-daily-log/review-list' => 'Review Daily Logs (/employee-task-daily-log/review-list)',
            ],
            'HR, Joining & Certificates' => [
                '/joining/*' => 'All Joining Module (/joining/*)',
                '/joining/admin/*' => 'All Joining Admin Actions (/joining/admin/*)',
                '/joining/admin/session-index' => 'Joining Sessions (/joining/admin/session-index)',
                '/joining/admin/session-create' => 'Create Joining Session (/joining/admin/session-create)',
                '/joining/admin/session-update' => 'Update Joining Session (/joining/admin/session-update)',
                '/joining/admin/document-index' => 'Session Documents (/joining/admin/document-index)',
                '/joining/admin/invitation-index' => 'Joining Invitations (/joining/admin/invitation-index)',
                '/joining/admin/invitation-create' => 'Create Invitation (/joining/admin/invitation-create)',
                '/joining/admin/send-invitation' => 'Send Invitation (/joining/admin/send-invitation)',
                '/joining/default/admin-index' => 'Joining Submissions (/joining/default/admin-index)',
                '/joining/default/admin-view' => 'View Joining Submission (/joining/default/admin-view)',
                '/joining-admin/*' => 'Legacy Joining Admin (/joining-admin/*)',
                '/joining-admin/session-index' => 'Legacy Joining Sessions (/joining-admin/session-index)',
                '/joining-admin/invitation-index' => 'Legacy Joining Invitations (/joining-admin/invitation-index)',
                '/joining/admin-index' => 'Legacy Joining Submissions (/joining/admin-index)',
                '/certificate/*' => 'Certificate Management (/certificate/*)',
                '/certificate/index' => 'Certificates List (/certificate/index)',
                '/certificate/upload' => 'Upload Certificate (/certificate/upload)',
                '/chat/index' => 'Internal Chat (/chat/index)',
            ],
            'Finance Module' => [
                '/finance/*' => 'All Finance Module (/finance/*)',
                '/finance/record/*' => 'All Financial Records (/finance/record/*)',
                '/finance/record/index' => 'Records Register (/finance/record/index)',
                '/finance/record/create' => 'Create Financial Record (/finance/record/create)',
                '/finance/record/view' => 'View Financial Record (/finance/record/view)',
                '/finance/record/approve' => 'Approve Financial Record (/finance/record/approve)',
                '/finance/record/reject' => 'Reject Financial Record (/finance/record/reject)',
                '/finance/record/delete' => 'Delete Financial Record (/finance/record/delete)',
                '/finance/head/*' => 'All Financial Heads Management (/finance/head/*)',
                '/finance/head/index' => 'Classification Heads List (/finance/head/index)',
                '/finance/head/create-major' => 'Create Major Head (/finance/head/create-major)',
                '/finance/head/update-major' => 'Update Major Head (/finance/head/update-major)',
                '/finance/head/create-main' => 'Create Main Head (/finance/head/create-main)',
                '/finance/head/update-main' => 'Update Main Head (/finance/head/update-main)',
                '/finance/head/create-sub' => 'Create Sub Head (/finance/head/create-sub)',
                '/finance/head/update-sub' => 'Update Sub Head (/finance/head/update-sub)',
            ],
        ];
    }

    /**
     * Automatically registers routes into the auth_item table (type = 2 permission)
     * matching the CMS dump format if they don't already exist.
     */
    public static function syncSystemRoutes()
    {
        $db = Yii::$app->db;
        $now = time();
        $routes = static::getSystemRoutes();

        foreach ($routes as $category => $items) {
            foreach ($items as $routeName => $label) {
                $exists = (new \yii\db\Query())
                    ->from('auth_item')
                    ->where(['name' => $routeName])
                    ->exists($db);

                if (!$exists) {
                    $db->createCommand()->insert('auth_item', [
                        'name' => $routeName,
                        'type' => 2, // Permission in Yii RBAC
                        'description' => $label,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->execute();
                }
            }
        }
    }

    /**
     * Links high-level permissions to their corresponding route wildcards in auth_item_child
     */
    public static function syncDefaultPermissionHierarchy()
    {
        try {
            $db = Yii::$app->db;
            $now = time();
            $mappings = [
                'manageChat' => ['/chat/*'],
                'manageCertificate' => ['/certificate/*'],
                'manageEmployee' => ['/employee/*'],
                'viewAllEmployee' => ['/employee/index', '/employee/view'],
                'viewOwnProfile' => ['/employee/my-profile'],
                'manageJoining' => ['/joining/*'],
                'manageFinancialRecords' => ['/finance/*'],
                'approveFinancialRecord' => ['/finance/record/approve', '/finance/record/reject'],
                'manageSales' => ['/sales/*'],
                'manageSalesAccounts' => ['/sales/invoice/*', '/sales/order/view', '/sales/report/*'],
                'viewSalesModule' => ['/sales/dashboard/index', '/sales/lead/index', '/sales/agenda/index'],
                'manageProject' => ['/project/*'],
                'manageTask' => ['/employee-task/*'],
                'submitTaskLog' => ['/employee-task-daily-log/my-logs', '/employee-task/my-tasks'],
                'reviewTaskLog' => ['/employee-task-daily-log/review-list'],
                'viewObservation' => ['/observation/*'],
                'manageRbac' => ['/users/*', '/rights/*'],
                'Rights' => ['/rights/*'],
            ];

            foreach ($mappings as $parent => $children) {
                $parentExists = (new \yii\db\Query())->from('auth_item')->where(['name' => $parent])->exists($db);
                if (!$parentExists) {
                    $db->createCommand()->insert('auth_item', [
                        'name' => $parent,
                        'type' => 2,
                        'description' => 'Permission ' . $parent,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->execute();
                }

                foreach ($children as $child) {
                    $childExists = (new \yii\db\Query())->from('auth_item')->where(['name' => $child])->exists($db);
                    if (!$childExists) {
                        $db->createCommand()->insert('auth_item', [
                            'name' => $child,
                            'type' => 2,
                            'description' => 'Route ' . $child,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])->execute();
                    }

                    $linkExists = (new \yii\db\Query())
                        ->from('auth_item_child')
                        ->where(['parent' => $parent, 'child' => $child])
                        ->exists($db);
                    if (!$linkExists) {
                        $db->createCommand()->insert('auth_item_child', [
                            'parent' => $parent,
                            'child' => $child,
                        ])->execute();
                    }
                }
            }
        } catch (\Throwable $e) {
            Yii::error('Permission hierarchy sync failed: ' . $e->getMessage(), __METHOD__);
        }
    }
}

