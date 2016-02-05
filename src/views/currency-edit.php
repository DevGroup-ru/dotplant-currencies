<?php
/**
 * @var \yii\widgets\ActiveForm $form
 * @var \DotPlant\Currencies\models\Currency $model
 */
use DotPlant\Currencies\models\CurrencyRateProvider;
use kartik\switchinput\SwitchInput;
use yii\helpers\ArrayHelper;
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
                <?= $form->field($model, 'is_main')->widget(SwitchInput::classname(), []) ?>
                <?= $form->field($model, 'name')->textInput(['maxlength' => 255, 'disabled' => !$model->isNewItem()]) ?>
                <?= $form->field($model, 'iso_code')->textInput(['maxlength' => 4]) ?>
                <?= $form->field($model, 'convert_nominal') ?>
                <?= $form->field($model, 'convert_rate') ?>
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
    <div class="col-sm-6">
        <div class="box box-solid">
            <div class="box-header with-border">
                <?= Icon::show('cog') ?>
                <h3 class="box-title"><?= Yii::t('dotplant.currencies', 'Currency formatting') ?></h3>
            </div>
            <div class="box-body">
                <?= $form->field($model, 'intl_formatting')->widget(SwitchInput::classname(), []) ?>
                <?= $form->field($model, 'min_fraction_digits') ?>
                <?= $form->field($model, 'max_fraction_digits') ?>
                <?= $form->field($model, 'dec_point') ?>
                <?= $form->field($model, 'thousands_sep') ?>
                <?= $form->field($model, 'format_string') ?>
                <?= $form->field($model, 'sort_order') ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel box box-primary">
            <div class="box-header with-border">
                <i class="fa fa-info"></i>
                <h3 class="box-title"><?= Yii::t('dotplant.currencies', 'Help tips')?></h3>
            </div>
            <div class="box-body">
                <blockquote>
                    <?= Yii::t(
                        'dotplant.currencies',
                        'Note that currencies formatting options will work only if option "Intl formatting with ICU" is in Off position. Otherwise currency will be formatted using yours server built in formatter!'
                    )?>
                </blockquote>
            </div>
        </div>
    </div>
</div>