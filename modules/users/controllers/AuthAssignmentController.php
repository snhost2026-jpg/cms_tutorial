<?php

namespace app\modules\users\controllers;

use Yii;
use app\modules\users\models\AuthAssignment;
use app\modules\users\models\AuthAssignmentSearch;
use app\models\User;
use app\components\AccessHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AuthAssignmentController provides CMS-style Dual Listbox assignment and CRUD
 * for auth_assignment and auth_item tables in the Users module.
 */
class AuthAssignmentController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'assign' => ['POST'],
                    'revoke' => ['POST'],
                    'assign-role' => ['POST'],
                    'revoke-role' => ['POST'],
                    'create-role' => ['POST'],
                    'sync-routes' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Enforce manageRbac permission
     */
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            return parent::beforeAction($action);
        }

        if (!Yii::$app->user->can('manageRbac') && !Yii::$app->user->can('SuperAdmin') && !AccessHelper::checkRoute('/*')) {
            throw new ForbiddenHttpException('You are not allowed to access Auth Assignment.');
        }

        return parent::beforeAction($action);
    }

    /**
     * Lists all AuthAssignment models.
     */
    public function actionIndex()
    {
        return $this->redirect(['/rights/assignment']);
    }

    /**
     * CMS-style User Assignment Dashboard:
     * Shows side-by-side Available vs Assigned listboxes with instant multi-select transfer.
     * @param int|string $user_id
     */
    public function actionUser($user_id)
    {
        return $this->redirect(['/rights/assignment/view', 'id' => $user_id]);
    }

    /**
     * AJAX endpoint to assign multiple items to a user at once (matching CMS yii2-admin).
     * @param int|string $id
     * @return array
     */
    public function actionAssign($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = User::findOne($id);
        if (!$user) {
            return ['error' => 'User not found'];
        }

        $items = Yii::$app->request->post('items', []);
        $now = time();
        $db = Yii::$app->db;
        $success = 0;

        foreach ($items as $name) {
            $name = trim((string)$name);
            if ($name === '') continue;

            try {
                // Ensure item exists in auth_item if it is a route
                $existsInItem = (new \yii\db\Query())
                    ->from('auth_item')
                    ->where(['name' => $name])
                    ->exists($db);

                if (!$existsInItem) {
                    $db->createCommand()->insert('auth_item', [
                        'name' => $name,
                        'type' => ($name[0] === '/' ? 2 : 1),
                        'description' => $name,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->execute();
                }

                $alreadyAssigned = (new \yii\db\Query())
                    ->from('auth_assignment')
                    ->where(['item_name' => $name, 'user_id' => (string)$user->id])
                    ->exists($db);

                if (!$alreadyAssigned) {
                    $db->createCommand()->insert('auth_assignment', [
                        'item_name' => $name,
                        'user_id' => (string)$user->id,
                        'created_at' => $now,
                    ])->execute();
                    $success++;
                }
            } catch (\Throwable $e) {
                Yii::error("Failed to assign {$name} to user {$user->id}: " . $e->getMessage());
            }
        }

        return $this->getUserAssignmentItems($user->id);
    }

    /**
     * AJAX endpoint to revoke multiple items from a user at once (matching CMS yii2-admin).
     * @param int|string $id
     * @return array
     */
    public function actionRevoke($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = User::findOne($id);
        if (!$user) {
            return ['error' => 'User not found'];
        }

        $items = Yii::$app->request->post('items', []);
        $db = Yii::$app->db;

        foreach ($items as $name) {
            $name = trim((string)$name);
            if ($name === '') continue;

            $db->createCommand()->delete('auth_assignment', [
                'item_name' => $name,
                'user_id' => (string)$user->id,
            ])->execute();
        }

        return $this->getUserAssignmentItems($user->id);
    }

    /**
     * Lists all system roles with assigned route counts and access management shortcuts.
     */
    public function actionRoles()
    {
        return $this->redirect(['/rights/role']);
    }

    /**
     * Dual-Listbox Role Assignment Dashboard:
     * Shows side-by-side Available vs Assigned listboxes with instant multi-select transfer for a role.
     * @param string $name Role name
     */
    public function actionRole($name)
    {
        return $this->redirect(['/rights/role/view', 'id' => $name]);
    }

    /**
     * AJAX endpoint to assign multiple page routes / permissions to a role.
     * @param string $name Role name
     * @return array
     */
    public function actionAssignRole($name)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $role = (new \yii\db\Query())
            ->from('auth_item')
            ->where(['name' => $name, 'type' => 1])
            ->one();

        if (!$role) {
            return ['error' => "Role '{$name}' not found"];
        }

        $items = Yii::$app->request->post('items', []);
        $now = time();
        $db = Yii::$app->db;

        foreach ($items as $itemName) {
            $itemName = trim((string)$itemName);
            if ($itemName === '' || $itemName === $name) continue;

            try {
                // Ensure item exists in auth_item if it's a route
                $existsInItem = (new \yii\db\Query())
                    ->from('auth_item')
                    ->where(['name' => $itemName])
                    ->exists($db);

                if (!$existsInItem) {
                    $db->createCommand()->insert('auth_item', [
                        'name' => $itemName,
                        'type' => ($itemName[0] === '/' ? 2 : 2),
                        'description' => $itemName,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->execute();
                }

                $alreadyChild = (new \yii\db\Query())
                    ->from('auth_item_child')
                    ->where(['parent' => $name, 'child' => $itemName])
                    ->exists($db);

                if (!$alreadyChild) {
                    $db->createCommand()->insert('auth_item_child', [
                        'parent' => $name,
                        'child' => $itemName,
                    ])->execute();
                }
            } catch (\Throwable $e) {
                Yii::error("Failed to assign {$itemName} to role {$name}: " . $e->getMessage());
            }
        }

        return $this->getRoleAssignmentItems($name);
    }

    /**
     * AJAX endpoint to revoke multiple page routes / permissions from a role.
     * @param string $name Role name
     * @return array
     */
    public function actionRevokeRole($name)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $role = (new \yii\db\Query())
            ->from('auth_item')
            ->where(['name' => $name, 'type' => 1])
            ->one();

        if (!$role) {
            return ['error' => "Role '{$name}' not found"];
        }

        $items = Yii::$app->request->post('items', []);
        $db = Yii::$app->db;

        foreach ($items as $itemName) {
            $itemName = trim((string)$itemName);
            if ($itemName === '') continue;

            $db->createCommand()->delete('auth_item_child', [
                'parent' => $name,
                'child' => $itemName,
            ])->execute();
        }

        return $this->getRoleAssignmentItems($name);
    }

    /**
     * Creates a new role.
     */
    public function actionCreateRole()
    {
        $name = trim((string)Yii::$app->request->post('name'));
        $description = trim((string)Yii::$app->request->post('description'));

        if ($name === '') {
            Yii::$app->session->setFlash('error', 'Role name cannot be empty.');
            return $this->redirect(['roles']);
        }

        $name = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '', $name));

        $exists = (new \yii\db\Query())
            ->from('auth_item')
            ->where(['name' => $name])
            ->exists();

        if ($exists) {
            Yii::$app->session->setFlash('warning', "Role '{$name}' already exists.");
            return $this->redirect(['role', 'name' => $name]);
        }

        $now = time();
        Yii::$app->db->createCommand()->insert('auth_item', [
            'name' => $name,
            'type' => 1,
            'description' => $description ?: ucfirst($name) . ' Role',
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        Yii::$app->session->setFlash('success', "Role '{$name}' created successfully. You can now configure page access.");
        return $this->redirect(['role', 'name' => $name]);
    }

    /**
     * Standard view action
     */
    public function actionView($item_name, $user_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($item_name, $user_id),
        ]);
    }

    /**
     * Creates new AuthAssignment models with multi-select support.
     */
    public function actionCreate($user_id = null)
    {
        $model = new AuthAssignment();
        if ($user_id !== null) {
            $model->user_id = (string)$user_id;
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('AuthAssignment', []);
            $targetUserId = trim((string)($post['user_id'] ?? ''));
            $itemNames = $post['item_names'] ?? [];

            if (empty($itemNames) && !empty($post['item_name'])) {
                $itemNames = (array)$post['item_name'];
            }

            if (empty($targetUserId)) {
                $model->addError('user_id', 'Please select a user.');
            } elseif (empty($itemNames)) {
                $model->addError('item_name', 'Please select at least one role, permission, or page route.');
            } else {
                $now = time();
                $insertedCount = 0;
                $db = Yii::$app->db;
                $tx = $db->beginTransaction();

                try {
                    foreach ((array)$itemNames as $item) {
                        $item = trim((string)$item);
                        if ($item === '') continue;

                        $itemExists = (new \yii\db\Query())
                            ->from('auth_item')
                            ->where(['name' => $item])
                            ->exists($db);

                        if (!$itemExists) {
                            $db->createCommand()->insert('auth_item', [
                                'name' => $item,
                                'type' => ($item[0] === '/' ? 2 : 1),
                                'description' => $item,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ])->execute();
                        }

                        $alreadyAssigned = (new \yii\db\Query())
                            ->from('auth_assignment')
                            ->where(['item_name' => $item, 'user_id' => $targetUserId])
                            ->exists($db);

                        if (!$alreadyAssigned) {
                            $db->createCommand()->insert('auth_assignment', [
                                'item_name' => $item,
                                'user_id' => $targetUserId,
                                'created_at' => $now,
                            ])->execute();
                            $insertedCount++;
                        }
                    }

                    $tx->commit();
                    Yii::$app->session->setFlash('success', "Assigned $insertedCount item(s) to user #$targetUserId successfully.");
                    return $this->redirect(['user', 'user_id' => $targetUserId]);
                } catch (\Throwable $e) {
                    $tx->rollBack();
                    Yii::$app->session->setFlash('error', 'Failed to save assignments: ' . $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'itemsList' => $this->getHierarchicalItemsList(),
            'usersList' => $this->getUsersList(),
        ]);
    }

    /**
     * Deletes an existing AuthAssignment model.
     */
    public function actionDelete($item_name, $user_id)
    {
        $this->findModel($item_name, $user_id)->delete();
        Yii::$app->session->setFlash('success', 'Auth Assignment removed.');

        return $this->redirect(['index']);
    }

    /**
     * Synchronize and populate standard page routes into auth_item table.
     */
    public function actionSyncRoutes()
    {
        try {
            AccessHelper::syncSystemRoutes();
            Yii::$app->session->setFlash('success', 'Page routes synchronized successfully into auth_item table.');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', 'Sync failed: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the AuthAssignment model based on primary key.
     */
    protected function findModel($item_name, $user_id)
    {
        if (($model = AuthAssignment::findOne(['item_name' => $item_name, 'user_id' => $user_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested Auth Assignment does not exist.');
    }

    /**
     * Returns separated available vs assigned items for a specific user (CMS Assignment structure).
     */
    protected function getUserAssignmentItems($userId)
    {
        $auth = Yii::$app->authManager;
        $available = [];

        // 1. Roles
        foreach ($auth->getRoles() as $role) {
            $available[$role->name] = 'role';
        }

        // 2. Existing Permissions in auth_item
        $itemsInDb = (new \yii\db\Query())
            ->from('auth_item')
            ->select(['name', 'type'])
            ->all();

        foreach ($itemsInDb as $row) {
            $name = $row['name'];
            if (!isset($available[$name])) {
                $available[$name] = ($row['type'] == 1 ? 'role' : ($name[0] === '/' ? 'route' : 'permission'));
            }
        }

        // 3. Known system routes
        foreach (AccessHelper::getSystemRoutes() as $group => $routes) {
            foreach (array_keys($routes) as $r) {
                if (!isset($available[$r])) {
                    $available[$r] = 'route';
                }
            }
        }

        // Fetch currently assigned items for this user
        $assignedRows = (new \yii\db\Query())
            ->from('auth_assignment')
            ->select(['item_name'])
            ->where(['user_id' => (string)$userId])
            ->column();

        $assigned = [];
        foreach ($assignedRows as $itemName) {
            $group = $available[$itemName] ?? ($itemName[0] === '/' ? 'route' : 'role');
            $assigned[$itemName] = $group;
            unset($available[$itemName]);
        }

        ksort($available);
        ksort($assigned);

        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * Returns grouped roles, permissions, and CMS-style page routes.
     */
    protected function getHierarchicalItemsList()
    {
        $auth = Yii::$app->authManager;
        $result = [];

        // 1. Roles
        $roles = [];
        foreach ($auth->getRoles() as $role) {
            $roles[$role->name] = $role->name . (!empty($role->description) ? ' (' . $role->description . ')' : '');
        }
        if (!empty($roles)) {
            $result['System Roles'] = $roles;
        }

        // 2. Standard RBAC Permissions
        $permissions = [];
        foreach ($auth->getPermissions() as $perm) {
            if ($perm->name === '' || $perm->name[0] !== '/') {
                $permissions[$perm->name] = $perm->name . (!empty($perm->description) ? ' — ' . $perm->description : '');
            }
        }
        if (!empty($permissions)) {
            $result['System Permissions'] = $permissions;
        }

        // 3. CMS Page-Wise Routes (Grouped by module)
        $systemRoutes = AccessHelper::getSystemRoutes();
        foreach ($systemRoutes as $category => $routes) {
            $result['Page Access: ' . $category] = $routes;
        }

        return $result;
    }

    /**
     * Returns available and assigned items for a role.
     * @param string $roleName
     * @return array
     */
    protected function getRoleAssignmentItems($roleName)
    {
        $available = [];

        // 1. Existing Permissions in auth_item
        $itemsInDb = (new \yii\db\Query())
            ->from('auth_item')
            ->select(['name', 'type'])
            ->all();

        foreach ($itemsInDb as $row) {
            $name = $row['name'];
            if ($name === $roleName) {
                continue;
            }
            if ($row['type'] == 1) {
                $available[$name] = 'role';
            } elseif ($name[0] === '/') {
                $available[$name] = 'route';
            } else {
                $available[$name] = 'permission';
            }
        }

        // 2. Known system routes from AccessHelper
        foreach (AccessHelper::getSystemRoutes() as $group => $routes) {
            foreach (array_keys($routes) as $r) {
                if (!isset($available[$r])) {
                    $available[$r] = 'route';
                }
            }
        }

        // 3. Fetch currently assigned children for this role from auth_item_child
        $assignedRows = (new \yii\db\Query())
            ->select(['child'])
            ->from('auth_item_child')
            ->where(['parent' => $roleName])
            ->column();

        $assigned = [];
        foreach ($assignedRows as $itemName) {
            $group = $available[$itemName] ?? ($itemName[0] === '/' ? 'route' : 'permission');
            $assigned[$itemName] = $group;
            unset($available[$itemName]);
        }

        ksort($available);
        ksort($assigned);

        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * Returns list of users for dropdown.
     */
    protected function getUsersList()
    {
        $users = User::find()->select(['id', 'username', 'email'])->orderBy(['username' => SORT_ASC])->all();
        $list = [];
        foreach ($users as $u) {
            $list[$u->id] = '#' . $u->id . ' - ' . $u->username . ($u->email ? ' (' . $u->email . ')' : '');
        }
        return $list;
    }
}
