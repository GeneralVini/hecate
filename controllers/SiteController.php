<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Division;
use app\models\Printer;
use app\models\Quota;
use yii\base\Module;
use yii\web\Controller;

/**
 * @extends Controller<Module>
 */
class SiteController extends Controller
{
    public function actionIndex(): string
    {
        $metrics = [
            'printers' => Printer::find()->count(),
            'printersOnline' => Printer::find()->where(['not', ['last_seen_at' => null]])->count(),
            'divisions' => Division::find()->count(),
            'quotaRows' => Quota::find()->count(),
        ];

        return $this->render('index', ['metrics' => $metrics]);
    }

    public function actionError(): string
    {
        return $this->render('error');
    }
}
