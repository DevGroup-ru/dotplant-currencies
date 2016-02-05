<?php

namespace DotPlant\Currencies\commands;

use DotPlant\Currencies\models\CurrencyRateProvider;
use DotPlant\Currencies\models\Currency;
use Ivory\HttpAdapter\CurlHttpAdapter;
use yii\console\Controller;
use Swap\Swap;
use Yii;
use app;

class CurrencyController extends Controller
{
    /**
     * @throws \Exception
     */
    public function actionUpdate()
    {
        $mainCurrency = Currency::getMainCurrency();
        if ($mainCurrency === null) {
            throw new \Exception("Main currency is not set");
        }
        $currencies = Currency::getUpdateable();
        $httpAdapter = new CurlHttpAdapter();
        /** @var Currency $currency */
        foreach ($currencies as $currency) {
            /** @var CurrencyRateProvider $providerModel */
            $providerModel = $currency->rateProvider;
            if (null !== $providerModel) {
                try {
                    $provider = $providerModel->getImplementationInstance($httpAdapter);
                    if (null !== $provider) {
                        $swap = new Swap($provider);
                        $rate = $swap->quote($currency->iso_code . '/' . $mainCurrency->iso_code)->getValue();
                        $currency->convert_rate = floatval($rate);
                        if ($currency->additional_rate > 0) {
                            // additional rate is in %
                            $currency->convert_rate *= (1 + $currency->additional_rate / 100);
                        }
                        if ($currency->additional_nominal !== 0) {
                            $currency->convert_rate += $currency->additional_nominal;
                        }
                        $currency->save();
                        echo $currency->iso_code . '/' . $mainCurrency->iso_code . ': ' . $rate . " == " . $currency->convert_rate . "\n";
                    }
                } catch (\Exception $e) {
                    echo "Error updating " . $currency->name . ': ' . $e->getMessage() . "\n\n";
                }
            }
        }
    }
}
