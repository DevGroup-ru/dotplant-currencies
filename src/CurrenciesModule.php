<?php
namespace DotPlant\Currencies;

use DotPlant\Currencies\helpers\CurrencyStorageHelper;
use DotPlant\Currencies\models\CurrencyRateProvider;
use DotPlant\Currencies\models\BaseFileModel;
use DotPlant\Currencies\models\Currency;
use yii\base\InvalidParamException;
use yii\base\Module;
use Yii;

class CurrenciesModule extends Module
{

    /** @var string Currency storage file */
    public $currenciesStorage = '@app/config/dp-currencies.php';

    /** @var string  CurrencyRateProvider storage file */
    public $providersStorage = '@app/config/dp-providers.php';

    /** @var string Currency[] cache key */
    public $currenciesCacheKey = 'dotplant.currencies.currenciesModels';

    /** @var string CurrencyRateProvider[] cache key */
    public $providersCacheKey = 'dotplant.currencies.CurrencyRateProvidersModels';

    /** @var array Currency[] & CurrencyRateProvider[] loaded set */
    private static $items = [];

    /** @var array default values for Currency[] & CurrencyRateProvider[] */
    private static $defaults = [];

    /** @var array  default values for Currency[] */
    private static $defaultCurrencies = [
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

    /** @var array  default values for CurrencyRateProvider[] */
    private static $defaultProviders = [
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

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$items = [
            Currency::className() => [],
            CurrencyRateProvider::className() => [],
        ];
        self::$defaults = [
            Currency::className() => self::$defaultCurrencies,
            CurrencyRateProvider::className() => self::$defaultProviders,
        ];
    }

    /**
     * Loads Currency[] & CurrencyRateProvider[] from associated storage files and
     * generates storage files if them not exists yet
     *
     * @param $className
     * @param bool $ignoreCache
     * @return mixed
     */
    public function getData($className, $ignoreCache = false)
    {
        if (false === class_exists($className)) {
            throw new InvalidParamException(
                Yii::t(
                    'dotplant.currencies',
                    'Class "{className}" not found!',
                    ['className' => $className]
                )
            );
        }
        /** @var BaseFileModel $model */
        $model = new $className;
        if (false === ($model instanceof BaseFileModel)) {
            throw new InvalidParamException(
                Yii::t(
                    'dotplant.currencies',
                    'Class "{className}" must be an instance of "BaseFileModel" !',
                    ['className' => $className]
                )
            );
        }
        if (0 === count(self::$items[$className]) || true === $ignoreCache) {
            $canLoad = false;
            $storage = $model->getStorage();
            if (true === file_exists($storage) && is_readable($storage)) {
                $canLoad = true;
            } else {
                $canLoad = CurrencyStorageHelper::generateStorage(self::$defaults[$className], $storage, $className);
            }
            if (true === $canLoad) {
                self::$items[$className] = include $storage;
            } else {
                Yii::$app->session->setFlash('error',
                    Yii::t(
                        'dotplant.currencies', 'Unable to write "{storage}" file.',
                        ['storage' => $storage]
                    )
                );
            }
        }
        return self::$items[$className];
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
