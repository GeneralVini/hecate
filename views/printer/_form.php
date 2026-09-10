<?php
use yii\\bootstrap5\\ActiveForm;
use yii\\helpers\\Html;
?>
<?php $form = ActiveForm::begin(); ?>
<?= $form->field($model, 'name')->textInput(['placeholder'=>'IMP-DCTIM-10-01']) ?>
<?= $form->field($model, 'host')->textInput(['placeholder'=>'IP ou FQDN']) ?>
<?= $form->field($model, 'location')->textInput(['placeholder'=>'Ex.: 4º andar']) ?>
<?= $form->field($model, 'enabled')->checkbox() ?>
<div class="mt-3"><?= Html::submitButton('Salvar', ['class' => 'btn btn-hecate']) ?> <?= Html::a('Cancelar', ['index'], ['class'=>'btn btn-outline-secondary']) ?></div>
<?php ActiveForm::end(); ?>
