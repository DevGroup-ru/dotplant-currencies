<?php
namespace DotPlant\Currencies;

use DotPlant\Currencies\helpers\CurrencyStorageHelper;
use DotPlant\Currencies\models\Currency;
use DotPlant\Currencies\models\CurrencyRateProvider;
use Yii;
use yii\base\Module;

class CurrenciesModule extends Module
{

    public $currenciesStorage = '@app/config/currencies.php';
    public $providersStorage = '@app/config/providers.php';
    public $currenciesCacheKey = 'dotplant.currencies.currenciesModels';
    public $providersCacheKey = 'dotplant.currencies.CurrencyRateProvidersModels';

    private static $currencies;
    private static $providers;

    private $defaultCurrencies = [
        'Ruble' => [
            'name' => 'Ruble',
            'iso_code' => 'RUB',
            'is_main' => 1,
            'format_string' => '# руб.',
            'intl_formatting' => 0,
        ],
        'US Dollar' => [
            'name' => 'US Dollar',
            'iso_code' => 'USD',
            'convert_nominal' => 1,
            'convert_rate' => 62.8353,
            'sort_order' => 1,
            'format_string' => '$ #',
            'thousands_sep' => '.',
            'dec_point' => ',',
        ],
        'Euro' => [
            'name' => 'Euro',
            'iso_code' => 'EUR',
            'convert_rate' => 71.3243,
            'format_string' => '&euro; #',
        ]
    ];

    private $defaultProviders = [
        [
            'name' => 'Google Finance',
            'class_name' => 'Swap\\Provider\\GoogleFinanceProvider',
        ],
        [
            'name' => 'Cbr Finance',
            'class_name' => 'DotPlant\Currencies\components\swap\provider\CbrFinanceProvider',
        ],
        [
            'name' => 'Currency rate multi provider',
            'class_name' => 'DotPlant\\Currencies\\components\\swap\\provider\\CurrencyRateMultiProvider',
            'params' => '{"mainProvider":"Google Finance","secondProvider":"Cbr Finance","criticalDifference":20}',
        ]
    ];


    public function getCurrencies($ignoreCache = false)
    {
        if (0 === count(self::$currencies) || true === $ignoreCache) {
            $canLoad = false;
            $fn = Yii::getAlias($this->currenciesStorage);
            if (true === file_exists($fn) && is_readable($fn)) {
                $canLoad = true;
            } else {
                $canLoad = CurrencyStorageHelper::generateStorage($this->defaultCurrencies, $fn, Currency::className());
            }
            if (true === $canLoad) {
                self::$currencies = include $fn;
            } else {
                Yii::$app->session->setFlash('error', Yii::t('dotplant.currencies', 'Unable to write currencies file'));
            }
        }
        return self::$currencies;
    }

    public function getProviders($ignoreCache = false)
    {
        if (0 === count(self::$providers) || true === $ignoreCache) {
            $canLoad = false;
            $fn = Yii::getAlias($this->providersStorage);
            if (true === file_exists($fn) && is_readable($fn)) {
                $canLoad = true;
            } else {
                $canLoad = CurrencyStorageHelper::generateStorage($this->defaultProviders, $fn, CurrencyRateProvider::className());
            }
            if (true === $canLoad) {
                self::$providers = include $fn;
            } else {
                Yii::$app->session->setFlash('error', Yii::t('dotplant.currencies', 'Unable to write providers file'));
            }
        }
        return self::$providers;
    }

    /**
     * @return self Module instance in application
     */
    public static function module()
    {
        $module = Yii::$app->getModule('currencies');
        if ($module === null) {
            $module = new self('currencies');
        }
        return $module;
    }


}
