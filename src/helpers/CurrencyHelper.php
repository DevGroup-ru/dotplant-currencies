<?php
namespace DotPlant\Currencies\helpers;

use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\models\Currency;
use Yii;

class CurrencyHelper
{
    /**
     * @var Currency $userCurrency
     * @var Currency $mainCurrency
     */
    protected static $userCurrency = null;
    protected static $mainCurrency = null;

    /**
     * @return Currency
     */
    public static function getMainCurrency()
    {
        return null === static::$mainCurrency
            ? static::$mainCurrency = Currency::getMainCurrency()
            : static::$mainCurrency;
    }

    /**
     * Finds Currency by ISO code. Return Currency[] if $all == true
     * if $all == false returns only first of found Currency
     * If there are no Currency with given code can return MainCurrency or null, depends on $useMainCurrency
     *
     * @param string $code
     * @param bool $useMainCurrency
     * @param bool $all
     * @return Currency|Currency[]|null
     */
    public static function findCurrencyByIso($code, $useMainCurrency = false, $all = false)
    {
        $currencies = Currency::findAll();
        $currencies = array_filter($currencies, function ($e) use ($code) {
            /** @var  Currency $e */
            return $code == $e->iso_code;
        });
        $out = null;
        if (false === empty($currencies)) {
            if (true === $all) {
                $out = $currencies;
            } else {
                $out = array_shift($currencies);
            }
        } else {
            if (true === $useMainCurrency) {
                $out = static::getMainCurrency();
            } else {
                $out = null;
            }
        }
        return $out;
    }

    /**
     * @param float|int $input
     * @param Currency $from
     * @param Currency $to
     * @param bool $format
     * @return float|int
     */
    public static function convertCurrencies($input = 0, Currency $from, Currency $to, $format = false)
    {
        if (0 === $input) {
            return $input;
        }
        if ($from->name != $to->name) {
            $main = static::getMainCurrency();
            if ($main->name == $from->name && $main->name != $to->name) {
                $input = $input / $to->convert_rate * $to->convert_nominal;
            } elseif ($main->name != $from->name && $main->name == $to->name) {
                $input = $input / $from->convert_nominal * $from->convert_rate;
            } else {
                $input = $input / $from->convert_nominal * $from->convert_rate;
                $input = $input / $to->convert_rate * $to->convert_nominal;
            }
        }
        $num = round($input, 2);
        if (true === $format) {
            $num = self::format($num, $to);
        }
        return $num;
    }

    /**
     * By default converts input number into MainCurrency and returns int | float
     * Using $format = true you can get formatted string according to Currency preferences
     *
     * @param float|int $input
     * @param Currency $from
     * @param bool $format
     * @return float|int
     */
    public static function convertToMainCurrency($input = 0, Currency $from, $format = false)
    {
        return static::convertCurrencies($input, $from, static::getMainCurrency(), $format);
    }

    /**
     * By default converts input number from MainCurrency into given Currency and returns int | float
     * Using $format = true you can get formatted string according to Currency preferences
     *
     * @param float|int $input
     * @param Currency $to
     * @param bool $format
     * @return float|int
     */
    public static function convertFromMainCurrency($input = 0, Currency $to, $format = false)
    {
        return static::convertCurrencies($input, static::getMainCurrency(), $to, $format);
    }

    /**
     * Formats price with current currency settings
     *
     * @param $price
     * @param Currency $currency
     * @return string
     */
    public static function format($price, Currency $currency)
    {
        if ($currency->intl_formatting == 1) {
            return $currency->getFormatter()->asCurrency($price);
        } else {
            $number_value = $currency->getFormatter()->asDecimal($price);
            return strtr($currency->format_string, ['#' => $number_value]);
        }
    }

    /**
     * @param Currency $currency
     * @param string|null $locale
     * @return string
     */
    public static function getCurrencySymbol(Currency $currency, $locale = null)
    {
        $locale = null === $locale ? Yii::$app->language : $locale;
        $result = '';
        try {
            $fake = $locale . '@currency=' . $currency->iso_code;
            $fmt = new \NumberFormatter($fake, \NumberFormatter::CURRENCY);
            $result = $fmt->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
        } catch (\Exception $e) {
            $result = preg_replace('%[\d\s,]%i', '', self::format(0, $currency));
        }
        return $result;
    }

    /**
     * Get a user currency from session
     * @return Currency
     */
    public static function getUserCurrency()
    {
        if (static::$userCurrency === null) {
            $isoCode = Yii::$app->session->get(CurrenciesModule::CURRENCY_SESSION_KEY);
            $userCurrency = CurrencyHelper::findCurrencyByIso($isoCode);
            static::$userCurrency = $userCurrency !== null ? $userCurrency : static::getMainCurrency();
        }
        return static::$userCurrency;
    }

    /**
     * Set a user currency to session
     * @param Currency $currency
     */
    public static function setUserCurrency($currency)
    {
        if ($currency instanceof Currency) {
            static::$userCurrency = $currency;
            Yii::$app->session->set(CurrenciesModule::CURRENCY_SESSION_KEY, $currency->iso_code);
        }
    }
}
