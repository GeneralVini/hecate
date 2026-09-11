<?php

declare(strict_types=1);

namespace App\Model;

use Override;
use Yiisoft\ActiveRecord\ActiveRecord;

final class Contract extends ActiveRecord
{
    public ?int $id = null;
    public string $name = '';
    public string $type = '';
    public ?int $bw_quota = null;
    public ?int $color_quota = null;
    public ?string $bw_unit_price = null;
    public ?string $color_unit_price = null;
    public ?string $bw_overage_price = null;
    public ?string $color_overage_price = null;
    public bool $active = true;

    #[Override]
    public function tableName(): string
    {
        return '{{%contract}}';
    }
}
