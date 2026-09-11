<?php

namespace app\models;

use yii\db\ActiveRecord;

class Printer extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%printer}}';
    }

    public function rules()
    {
        return [
            [['name', 'host'], 'required'],
            [['name', 'host', 'vendor', 'model', 'location', 'monitor_source'], 'string', 'max' => 160],
            [['is_color', 'is_duplex', 'enabled'], 'boolean'],
            [['last_seen_at'], 'safe'],
        ];
    }
}
