<?php

declare(strict_types=1);

namespace app\models;

use yii\web\IdentityInterface;

class User implements IdentityInterface
{
    public static function findIdentity($id): ?IdentityInterface
    {
        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public function getId(): int|string
    {
        return '';
    }

    public function getAuthKey(): string
    {
        return '';
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }
}
