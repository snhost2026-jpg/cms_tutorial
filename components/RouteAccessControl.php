<?php

namespace app\components;

use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

/**
 * RouteAccessControl enforces page-wise route permissions matching CMS access control.
 * It checks whether the user has access to the current controller action route.
 */
class RouteAccessControl extends ActionFilter
{
    /**
     * Actions or routes that are always allowed without restriction
     */
    public $allowActions = [
        'site/login',
        'site/logout',
        'site/error',
        'site/captcha',
    ];

    /**
     * Actions allowed for any authenticated user.
     */
    public $authenticatedAllowActions = [
        'site/index',
        'site/about',
        'site/contact',
        'site/change-password',
    ];

    /**
     * Fallback mapping from route prefixes/names to legacy RBAC permissions
     * used when a user/role has no explicit route-level assignments yet.
     */
    private function hasLegacyPermissionForRoute(string $uniqueId): bool
    {
        $map = [
            'users/*' => 'manageRbac',
            'user/*' => 'manageRbac',
            'auth-assignment/*' => 'manageRbac',
            'rights/*' => 'manageRbac',
        ];

        foreach ($map as $pattern => $permission) {
            if ($this->matchesPattern($uniqueId, $pattern) && Yii::$app->user->can($permission)) {
                return true;
            }
        }
        return false;
    }

    private function matchesPattern(string $uniqueId, string $pattern): bool
    {
        if (substr($pattern, -1) === '*') {
            return str_starts_with($uniqueId, rtrim($pattern, '*'));
        }
        return $uniqueId === $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $uniqueId = $action->getUniqueId();

        // Skip error action and explicitly allowed public actions
        if ($uniqueId === Yii::$app->getErrorHandler()->errorAction) {
            return true;
        }

        foreach ($this->allowActions as $pattern) {
            if ($this->matchesPattern($uniqueId, $pattern)) {
                return true;
            }
        }

        // Guests must log in
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->loginRequired();
            return false;
        }

        foreach ($this->authenticatedAllowActions as $pattern) {
            if ($this->matchesPattern($uniqueId, $pattern)) {
                return true;
            }
        }

        // SuperAdmin / admin always allowed
        if (Yii::$app->user->can('SuperAdmin') || Yii::$app->user->can('admin') || AccessHelper::checkRoute('/*')) {
            return true;
        }

        // Check page/route-level access
        if (AccessHelper::checkRoute('/' . $uniqueId)) {
            return true;
        }

        // If the user has no explicit route assignments yet, honor legacy RBAC permissions
        if (empty(AccessHelper::getAssignedRoutesByUser(Yii::$app->user->id)) && $this->hasLegacyPermissionForRoute($uniqueId)) {
            return true;
        }

        // Fall back to Yii's standard controller behaviors / RBAC permissions if page route isn't strictly defined
        // If an explicit route was checked and failed, throw 403
        try {
            $routePermissionExists = (new \yii\db\Query())
                ->from('auth_item')
                ->where(['name' => ['/' . $uniqueId, '/' . $action->controller->getUniqueId() . '/*']])
                ->exists();

            if ($routePermissionExists) {
                throw new ForbiddenHttpException('You do not have permission to access this page (' . $uniqueId . ').');
            }
        } catch (\yii\db\Exception $e) {
            // Table might not exist yet during initial setup
        }

        return true;
    }
}
