<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Modelo da impressora gerenciada pelo HECATE.
 *
 * @property int $id
 * @property string $name
 * @property string $host
 * @property string|null $vendor
 * @property string|null $model
 * @property string|null $location
 * @property bool $is_color
 * @property bool $is_duplex
 * @property string|null $monitor_source
 * @property string|null $last_seen_at
 * @property bool $enabled
 * @property string|null $created_at
 */
class Printer extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%printer}}';
    }

    public function rules(): array
    {
        return [
            [['name', 'host'], 'required'],
            [['name', 'host', 'vendor', 'model', 'location', 'monitor_source'], 'string', 'max' => 160],
            [['is_color', 'is_duplex', 'enabled'], 'boolean'],
            [['last_seen_at'], 'safe'],
        ];
    }
}
