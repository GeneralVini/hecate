<?php

declare(strict_types=1);

namespace App\Model;

use Override;
use Yiisoft\ActiveRecord\ActiveRecord;

final class Quota extends ActiveRecord
{
    public ?int $id = null;
    public int $division_id = 0;
    public string $period = '';
    public int $bw_allocated = 0;
    public int $bw_used = 0;
    public int $bw_reserved = 0;
    public int $color_allocated = 0;
    public int $color_used = 0;
    public int $color_reserved = 0;

    #[Override]
    public function tableName(): string
    {
        return '{{%quota}}';
    }
}
