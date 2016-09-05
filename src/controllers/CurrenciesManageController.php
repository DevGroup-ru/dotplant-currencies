<?php
namespace DotPlant\Currencies\controllers;

use DevGroup\AdminUtils\controllers\BaseController;
use DotPlant\Currencies\actions\ItemDeleteAction;
use DotPlant\Currencies\actions\ItemEditAction;
use DotPlant\Currencies\actions\ResetAction;
use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\models\Currency;
use Yii;

class CurrenciesManageController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'edit' => [
                'class' => ItemEditAction::className(),
                'className' => Currency::className(),
                'itemName' => Yii::t('dotplant.currencies', 'currency'),
                'editView' => '@vendor/dotplant/currencies/src/views/currency-edit',
                'storage' => CurrenciesModule::module()->currenciesStorage,
            ],
            'delete' => [
                'class' => ItemDeleteAction::className(),
                'className' => Currency::className(),
                'itemName' => Yii::t('dotplant.currencies', 'currency'),
                'storage' => CurrenciesModule::module()->currenciesStorage,
            ],
            'reset' => [
                'class' => ResetAction::className(),
                'className' => Currency::className(),
                'itemName' => Yii::t('dotplant.currencies', 'currencies'),
                'storage' => CurrenciesModule::module()->currenciesStorage,
            ],
        ];
    }
}