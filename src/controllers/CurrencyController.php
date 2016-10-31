<?php

namespace DotPlant\Currencies\controllers;

use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\events\AfterUserCurrencyChangeEvent;
use DotPlant\Currencies\helpers\CurrencyHelper;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class CurrencyController extends Controller
{
    public function actionChange()
    {
        $isoCode = Yii::$app->request->post('isoCode');
        if ($isoCode === null) {
            throw new BadRequestHttpException;
        }
        $newUserCurrency = CurrencyHelper::findCurrencyByIso($isoCode);
        if ($newUserCurrency !== null) {
            $oldUserCurrency = CurrencyHelper::getUserCurrency();
            CurrencyHelper::setUserCurrency($newUserCurrency);
            $event = new AfterUserCurrencyChangeEvent;
            $event->oldUserCurrency = $oldUserCurrency;
            $event->newUserCurrency = $newUserCurrency;
            CurrenciesModule::module()->trigger(CurrenciesModule::AFTER_USER_CURRENCY_CHANGE, $event);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}
