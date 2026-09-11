<?php

namespace app\models;

use yii\db\ActiveRecord;

class Contract extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%contract}}';
    }

    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['name'], 'string', 'max' => 160],
            [['type'], 'in', 'range' => ['CONSUMPTION', 'MONTHLY_QUOTA']],
            [['bw_quota', 'color_quota'], 'integer'],
            [
                ['bw_unit_price', 'color_unit_price', 'bw_overage_price', 'color_overage_price'],
                'number',
            ],
        ];
    }
}
