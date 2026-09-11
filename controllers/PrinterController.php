<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Printer;
use LogicException;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Application;
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
        $application = $this->webApplication();
        $postData = (array) $application->getRequest()->post();

        if ($model->load($postData) && $model->save()) {
            $application->getSession()->setFlash(
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
        $application = $this->webApplication();

        $application->getSession()->setFlash(
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

    private function webApplication(): Application
    {
        $application = Yii::$app;
        if (!$application instanceof Application) {
            throw new LogicException('PrinterController requer uma aplicação web do Yii.');
        }

        return $application;
    }
}
