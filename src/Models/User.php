<?php

namespace PXP\Models;

use PXP\Data\Model;

/**
 * @property int $id
 * @property string $username
 * @property string $password_hash
 */
class User extends Model
{
    protected string $table = 'users';

    public function setPasswordHash(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }
}
