<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Printer;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PrinterController extends Controller
{
    public function actionIndex(): string
    {
        $provider = new ActiveDataProvider([
            'query' => Printer::find()->orderBy(['name' => SORT_ASC]),
        ]);

        return $this->render('index', ['provider' => $provider]);
    }

    public function actionCreate(): string|Response
    {
        $model = new Printer(['enabled' => true]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(
                'success',
                'Impressora cadastrada. Execute a detecção automática para completar os dados.'
            );

            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionDetect(int $id): Response
    {
        $model = $this->findModel($id);
        Yii::$app->session->setFlash(
            'info',
            'MVP: solicitação de detecção registrada para ' . $model->name .
            '. A integração com hecate-agent será implementada na próxima etapa.'
        );

        return $this->redirect(['index']);
    }

    private function findModel(int $id): Printer
    {
        $model = Printer::findOne($id);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Impressora não encontrada.');
    }
}
