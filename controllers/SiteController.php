<?php

namespace app\\controllers;

use app\\models\\Printer;
use app\\models\\Division;
use app\\models\\Quota;
use yii\\web\\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        $metrics = [
            'printers' => Printer::find()->count(),
            'printersOnline' => Printer::find()->where(['not', ['last_seen_at' => null]])->count(),
            'divisions' => Division::find()->count(),
            'quotaRows' => Quota::find()->count(),
        ];

        return $this->render('index', ['metrics' => $metrics]);
    }

    public function actionError()
    {
        return $this->render('error');
    }
}
