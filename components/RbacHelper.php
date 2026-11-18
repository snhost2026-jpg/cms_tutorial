<?php
namespace app\components;

use Yii;

class RbacHelper
{
    public static function can(string $permission): bool
    {
        return !Yii::$app->user->isGuest && Yii::$app->user->can($permission);
    }

    public static function role(): ?string
    {
        if (Yii::$app->user->isGuest) {
            return null;
        }

        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
        return empty($roles) ? null : array_key_first($roles);
    }
}
