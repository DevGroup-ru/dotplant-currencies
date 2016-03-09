<?php

namespace DotPlant\Currencies\models;

use DotPlant\Currencies\CurrenciesModule;
use yii\base\InvalidConfigException;
use Yii;

/**
 * This is the model class Multi-currency
 *
 * @property string $name
 * @property string $iso_code
 * @property integer $is_main
 * @property double $convert_nominal
 * @property double $convert_rate
 * @property integer $sort_order
 * @property integer $intl_formatting
 * @property integer $min_fraction_digits
 * @property integer $max_fraction_digits
 * @property string $dec_point
 * @property string $thousands_sep
 * @property string $format_string
 * @property double $additional_rate
 * @property double $additional_nominal
 * @property integer $currency_rate_provider_name
 */
class Currency extends BaseFileModel
{
    public $name;
    public $iso_code;
    public $is_main;
    public $convert_nominal;
    public $convert_rate;
    public $sort_order;
    public $intl_formatting;
    public $min_fraction_digits;
    public $max_fraction_digits;
    public $dec_point;
    public $thousands_sep;
    public $format_string;
    public $additional_rate;
    public $additional_nominal;
    public $currency_rate_provider_name;

    private static $mainCurrency = null;
    private $formatter = null;

    protected static $models = [];
    protected static $cacheKey;
    protected static $storage;

    protected $defaults = [
        'intl_formatting' => 1,
        'convert_nominal' => 1,
        'convert_rate' => 1,
        'sort_order' => 1,
        'min_fraction_digits' => 1,
        'is_main' => 0,
        'additional_rate' => 0,
        'additional_nominal' => 0,
        'max_fraction_digits' => 2,
        'dec_point' => '.',
        'thousands_sep' => ' ',
        'currency_rate_provider_name' => null,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$cacheKey = CurrenciesModule::module()->currenciesCacheKey;
        self::$storage = Yii::getAlias(CurrenciesModule::module()->currenciesStorage);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'name',
                'checkNameIsUnique',
                'skipOnEmpty' => false,
                'skipOnError' => false,
                'on' => self::SCENARIO_NEW,
            ],
            [['name', 'iso_code', 'format_string'], 'required'],
            [
                [
                    'is_main',
                    'sort_order',
                    'intl_formatting',
                    'min_fraction_digits',
                    'max_fraction_digits',
                ],
                'integer'
            ],
            [['convert_nominal', 'convert_rate', 'additional_rate', 'additional_nominal'], 'number'],
            [
                [
                    'currency_rate_provider_name',
                    'name',
                    'iso_code',
                    'dec_point',
                    'thousands_sep',
                    'format_string'
                ],
                'string',
                'max' => 255
            ],
            [
                [
                    'intl_formatting',
                    'convert_nominal',
                    'convert_rate',
                    'sort_order',
                    'min_fraction_digits',
                ],
                'default',
                'value' => 1
            ],
            [['is_main', 'additional_rate', 'additional_nominal'], 'default', 'value' => 0],
            ['max_fraction_digits', 'default', 'value' => 2],
            ['dec_point', 'default', 'value' => '.'],
            ['thousands_sep', 'default', 'value' => ' '],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('dotplant.currencies', 'Name'),
            'iso_code' => Yii::t('dotplant.currencies', 'ISO-4217 code'),
            'is_main' => Yii::t('dotplant.currencies', 'Is main currency'),
            'convert_nominal' => Yii::t('dotplant.currencies', 'Convert nominal'),
            'convert_rate' => Yii::t('dotplant.currencies', 'Convert rate'),
            'sort_order' => Yii::t('dotplant.currencies', 'Sort Order'),
            'intl_formatting' => Yii::t('dotplant.currencies', 'Intl formatting with ICU'),
            'min_fraction_digits' => Yii::t('dotplant.currencies', 'Min fraction digits'),
            'max_fraction_digits' => Yii::t('dotplant.currencies', 'Max fraction digits'),
            'dec_point' => Yii::t('dotplant.currencies', 'Decimal point'),
            'thousands_sep' => Yii::t('dotplant.currencies', 'Thousands separator'),
            'format_string' => Yii::t('dotplant.currencies', 'Format string'),
            'additional_rate' => Yii::t('dotplant.currencies', 'Additional rate'),
            'additional_nominal' => Yii::t('dotplant.currencies', 'Additional nominal'),
            'currency_rate_provider_name' => Yii::t('dotplant.currencies', 'Currency rate provider'),
        ];
    }

    /**
     * Returns main currency object for this shop with static-cache
     *
     * @return Currency Main currency object
     */
    public static function getMainCurrency()
    {
        if (true === empty(self::$mainCurrency) || false === self::$mainCurrency instanceof self) {
            self::findAll();
            $mains = array_filter(self::$models, function ($i) {
                return $i->is_main == 1;
            });
            self::$mainCurrency = array_shift($mains);
        }
        return self::$mainCurrency;
    }

    /**
     * Relation to CurrencyRateProvider model
     *
     * @return CurrencyRateProvider
     */
    public function getRateProvider()
    {
        return CurrencyRateProvider::getByName($this->currency_rate_provider_name);
    }

    /**
     * Returns \yii\i18n\Formatter instance for current Currency instance
     * @return \yii\i18n\Formatter
     * @throws InvalidConfigException
     */
    public function getFormatter()
    {
        if ($this->formatter === null) {
            $this->formatter = Yii::createObject([
                'class' => '\yii\i18n\Formatter',
                'currencyCode' => $this->iso_code,
                'decimalSeparator' => $this->dec_point,
                'thousandSeparator' => $this->thousands_sep,
                'numberFormatterOptions' => [
                    7 => $this->min_fraction_digits, // min
                    6 => $this->max_fraction_digits, // max
                ]
            ]);
        }
        return $this->formatter;
    }

    /**
     * Returns Currency [] can by automatically updated using associated rate provider
     *
     * @return array
     */
    public static function getUpdateable()
    {
        self::findAll();
        return array_filter(self::$models, function ($item) {
            /** @var Currency $item */
            return false === empty($item->currency_rate_provider_name);
        });
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (true === parent::beforeSave($insert)) {
            if (1 == $this->is_main) {
                $currencies = self::findAll();
                /** @var self $currency */
                foreach ($currencies as $currency) {
                    if ($this->name == $currency->name) {
                        continue;
                    }
                    $currency->is_main = 0;
                    $currency->save();
                }
            }
        }
        return true;
    }
}
