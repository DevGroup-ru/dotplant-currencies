<?php
namespace DotPlant\Currencies\controllers;

use DotPlant\Currencies\actions\ItemDeleteAction;
use DotPlant\Currencies\actions\ResetAction;
use DotPlant\Currencies\CurrenciesModule;
use Yii;
use DevGroup\AdminUtils\controllers\BaseController;
use DotPlant\Currencies\actions\ItemEditAction;
use DotPlant\Currencies\models\CurrencyRateProvider;

class CurrencyRateProviderController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'edit' => [
                'class' => ItemEditAction::className(),
                'className' => CurrencyRateProvider::className(),
                'itemName' => Yii::t('dotplant.currencies', 'currency rate provider'),
                'editView' => '@vendor/dotplant/currencies/src/views/provider-edit',
                'storage' => CurrenciesModule::module()->providersStorage,
            ],
            'delete' => [
                'class' => ItemDeleteAction::className(),
                'className' => CurrencyRateProvider::className(),
                'itemName' => Yii::t('dotplant.currencies', 'currency rate provider'),
                'storage' => CurrenciesModule::module()->providersStorage,
            ],
            'reset' => [
                'class' => ResetAction::className(),
                'className' => CurrencyRateProvider::className(),
                'itemName' => Yii::t('dotplant.currencies', 'currency rate providers'),
                'storage' => CurrenciesModule::module()->providersStorage,
            ],
        ];
    }
}