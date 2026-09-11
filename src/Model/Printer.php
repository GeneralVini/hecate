<?php

declare(strict_types=1);

namespace App\Model;

use Override;
use Yiisoft\ActiveRecord\ActiveRecord;

final class Printer extends ActiveRecord
{
    public ?int $id = null;
    public string $name = '';
    public string $host = '';
    public ?string $vendor = null;
    public ?string $model = null;
    public ?string $location = null;
    public bool $is_color = false;
    public bool $is_duplex = false;
    public ?string $monitor_source = null;
    public ?string $last_seen_at = null;
    public bool $enabled = true;
    public ?string $created_at = null;

    #[Override]
    public function tableName(): string
    {
        return '{{%printer}}';
    }
}
