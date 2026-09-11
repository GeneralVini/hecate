<?php

namespace app\models;

use yii\db\ActiveRecord;

class Division extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%division}}';
    }

    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 120],
            [['code'], 'unique'],
        ];
    }
}
