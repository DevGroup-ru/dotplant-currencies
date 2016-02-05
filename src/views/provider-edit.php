<?php
/**
 * @var \yii\widgets\ActiveForm $form
 * @var \DotPlant\Currencies\models\CurrencyRateProvider $model
 */
?>
<div class="row">
    <div class="col-sm-6">
        <?= $form->field($model, 'name')->textInput(['disabled' => !$model->isNewItem()]) ?>
        <?= $form->field($model, 'class_name') ?>
        <?= $form->field($model, 'params')->textarea() ?>
    </div>
</div>