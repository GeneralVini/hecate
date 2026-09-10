<?php
use yii\\grid\\GridView;
use yii\\helpers\\Html;
$this->title = 'Impressoras';
?>
<div class="d-flex justify-content-between align-items-center mb-3"><div><h1 class="h3 mb-0">Impressoras</h1><small class="text-muted">Cadastro e descoberta automática do parque da OM.</small></div><?= Html::a('Nova impressora', ['create'], ['class' => 'btn btn-hecate']) ?></div>
<div class="panel-card"><?= GridView::widget(['dataProvider' => $provider, 'columns' => ['name','host','vendor','model','location',['attribute'=>'is_color','format'=>'boolean'],['attribute'=>'last_seen_at','format'=>'datetime'],['class'=>'yii\\grid\\ActionColumn','template'=>'{detect}','buttons'=>['detect'=>fn($url,$model)=>Html::a('Detectar',['detect','id'=>$model->id],['class'=>'btn btn-sm btn-outline-primary','data-method'=>'post'])]]]]) ?></div>
