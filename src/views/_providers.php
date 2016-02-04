<?php
use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\models\CurrencyRateProvider;;
use yii\helpers\Html;
use kartik\icons\Icon;
use DevGroup\AdminUtils\Helper;
use yii\grid\GridView;

$currencies = CurrenciesModule::module()->getProviders();
$currenciesProvider = new \yii\data\ArrayDataProvider([
        'allModels' => CurrencyRateProvider::findAll(),
        'pagination' => [
            'pageSize' => 10,
        ],
    ]
);
$currencyButtons =
    Html::tag('div',
        Html::a(
            Icon::show('plus') . '&nbsp;' . Yii::t('dotplant.currencies', 'Add provider'),
            ['/providers/edit', 'returnUrl' => Helper::returnUrl()],
            ['role' => 'button', 'class' => 'btn btn-success']
        )
        . Html::a(
            Icon::show('eraser') . '&nbsp;' . Yii::t('dotplant.currencies', 'Reset providers'),
            ['/providers/reset', 'returnUrl' => Helper::returnUrl()],
            ['role' => 'button', 'class' => 'btn btn-danger']),
        ['class' => 'btn-group pull-right', 'role' => 'group', 'aria-label' => 'Providers buttons']
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
    'id' => 'dotplant-currencies-providers-list',
    'dataProvider' => $currenciesProvider,
    'layout' => str_replace('[button]', $currencyButtons, $gridTpl),
    'tableOptions' => [
        'class' => 'table table-bordered table-hover table-responsive',
    ],
    'columns' => [
        'name',
        'class_name',
        [
            'class' => 'DevGroup\AdminUtils\columns\ActionColumn',
            'options' => [
                'width' => '95px',
            ],
            'buttons' => [
                [
                    'url' => '/providers/edit',
                    'icon' => 'pencil',
                    'class' => 'btn-primary',
                    'label' => Yii::t('dotplant.currencies', 'Edit'),
                ],
                [
                    'url' => '/providers/delete',
                    'icon' => 'trash-o',
                    'class' => 'btn-danger',
                    'label' => Yii::t('dotplant.currencies', 'Delete'),
                ],
            ],
        ],
    ],
]);