<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Printer;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\Session;

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

        /** @var Request $request */
        $request = Yii::$app->getRequest();

        if ($model->load($request->post()) && $model->save()) {
            /** @var Session $session */
            $session = Yii::$app->getSession();
            $session->setFlash(
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

        /** @var Session $session */
        $session = Yii::$app->getSession();
        $session->setFlash(
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
