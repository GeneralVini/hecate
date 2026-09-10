<?php

namespace app\\models;

use yii\\db\\ActiveRecord;

class Quota extends ActiveRecord
{
    public static function tableName() { return '{{%quota}}'; }
    public function rules()
    {
        return [
            [['division_id', 'period'], 'required'],
            [['division_id', 'bw_allocated', 'bw_used', 'bw_reserved', 'color_allocated', 'color_used', 'color_reserved'], 'integer'],
            [['period'], 'string', 'max' => 7],
        ];
    }
}
