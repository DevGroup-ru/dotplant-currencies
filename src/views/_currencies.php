<?php
use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\models\Currency;
use DevGroup\AdminUtils\Helper;
use yii\grid\GridView;
use kartik\icons\Icon;
use yii\helpers\Html;

$currencies = CurrenciesModule::module()->getData(Currency::className());
$currenciesProvider = new \yii\data\ArrayDataProvider([
        'allModels' => Currency::findAll(),
        'pagination' => [
            'pageSize' => 10,
        ],
    ]
);
$currencyButtons =
    Html::tag('div',
        Html::a(
            Icon::show('plus') . '&nbsp;' . Yii::t('dotplant.currencies', 'Add currency'),
            ['/currencies/edit', 'returnUrl' => Helper::returnUrl()],
            ['role' => 'button', 'class' => 'btn btn-success']
        )
        . Html::a(
            Icon::show('eraser') . '&nbsp;' . Yii::t('dotplant.currencies', 'Reset currencies'),
            ['/currencies/reset', 'returnUrl' => Helper::returnUrl()],
            ['role' => 'button', 'class' => 'btn btn-danger']),
        ['class' => 'btn-group pull-right', 'role' => 'group', 'aria-label' => 'Currencies buttons']
    );
$gridTpl = <<<TPL
<div class="box-body">
    {items}
</div>
<div class="box-footer">
    <div class="row ext-bottom">
        <div class="col-sm-5">
            {summary}
        </div>
        <div class="col-sm-7">
            {pager}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">[button]</div>
    </div>
</div>
TPL;
echo GridView::widget([
    'id' => 'dotplant-currencies-list',
    'dataProvider' => $currenciesProvider,
    'layout' => str_replace('[button]', $currencyButtons, $gridTpl),
    'tableOptions' => [
        'class' => 'table table-bordered table-hover table-responsive',
    ],
    'columns' => [
        'name',
        'iso_code',
        [
            'attribute' => 'is_main',
            'content' => function ($data) {
                return Yii::$app->formatter->asBoolean($data->is_main);
            }
        ],
        'convert_nominal',
        'convert_rate',
        'currency_rate_provider_name',
        [
            'class' => 'DevGroup\AdminUtils\columns\ActionColumn',
            'options' => [
                'width' => '95px',
            ],
            'buttons' => [
                [
                    'url' => '/currencies/edit',
                    'icon' => 'pencil',
                    'class' => 'btn-primary',
                    'label' => Yii::t('dotplant.currencies', 'Edit'),
                ],
                [
                    'url' => '/currencies/delete',
                    'icon' => 'trash-o',
                    'class' => 'btn-danger',
                    'label' => Yii::t('dotplant.currencies', 'Delete'),
                ],
            ],
        ],
    ],
]);