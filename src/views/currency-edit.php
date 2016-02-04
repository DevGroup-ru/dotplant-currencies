<?php
/**
 * @var \yii\widgets\ActiveForm $form
 * @var \DotPlant\Currencies\models\Currency $model
 */
use DotPlant\Currencies\models\CurrencyRateProvider;
use yii\helpers\ArrayHelper;
use kartik\switchinput\SwitchInput;
use kartik\icons\Icon;

$providers = ArrayHelper::map(CurrencyRateProvider::findAll(), 'name', 'name');
$switchOptions = [
    0 => Yii::$app->formatter->asBoolean(0),
    1 => Yii::$app->formatter->asBoolean(1),
];

?>
<div class="row">
    <div class="col-sm-6">
        <div class="box box-solid">
            <div class="box-header with-border">
                <?= Icon::show('money') ?>
                <h3 class="box-title"><?= Yii::t('dotplant.currencies', 'Currency') ?></h3>
            </div>
            <div class="box-body">
                <?= $form->field($model, 'is_main')->textInput(['maxlength' => 255])->widget(SwitchInput::classname(), []) ?>
                <?= $form->field($model, 'name')->textInput(['disabled' => $model->isNewItem()]) ?>
                <?= $form->field($model, 'iso_code')->textInput(['maxlength' => 4]) ?>
                <?= $form->field($model, 'convert_nominal') ?>
                <?= $form->field($model, 'convert_rate') ?>
                <?= $form->field($model, 'sort_order') ?>
                <?= $form->field($model, 'min_fraction_digits') ?>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="box box-solid">
            <div class="box-header with-border">
                <?= Icon::show('cog') ?>
                <h3 class="box-title"><?= Yii::t('dotplant.currencies', 'Currency formatting') ?></h3>
            </div>
            <div class="box-body">
                <?= $form->field($model, 'intl_formatting')->widget(SwitchInput::classname(), []) ?>
                <?= $form->field($model, 'max_fraction_digits') ?>
                <?= $form->field($model, 'dec_point') ?>
                <?= $form->field($model, 'thousands_sep') ?>
                <?= $form->field($model, 'format_string') ?>
                <?= $form->field($model, 'additional_rate') ?>
                <?= $form->field($model, 'additional_nominal') ?>
                <?= $form->field($model, 'currency_rate_provider_name')->dropDownList(
                    $providers,
                    [
                        'prompt' => Yii::t('dotplant.currencies', 'Select provider')
                    ]
                ) ?>
            </div>
        </div>
    </div>
</div>