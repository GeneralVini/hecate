<?php

declare(strict_types=1);

namespace App\Model;

use Yiisoft\ActiveRecord\ActiveRecord;

final class Division extends ActiveRecord
{
    public ?int $id = null;
    public string $code = '';
    public string $name = '';
    public ?string $created_at = null;

    public function tableName(): string
    {
        return '{{%division}}';
    }
}
