<?php
namespace DotPlant\Currencies\helpers;

use DevGroup\ExtensionsManager\helpers\ApplicationConfigWriter;
use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\models\Currency;
use DotPlant\Currencies\models\CurrencyRateProvider;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\Object;
use Yii;

class CurrencyStorageHelper extends Object
{
    /**
     * Removes given model item from items storage
     *
     * @param Currency | CurrencyRateProvider $toRemove
     * @param string $storage storage file path
     * @return bool
     */
    public static function removeFromStorage($toRemove, $storage)
    {
        $method = self::getMethod($toRemove);
        $current = CurrenciesModule::module()->$method(true);
        unset($current[$toRemove->name]);
        return self::generateStorage($current, $storage, $toRemove::className());
    }

    /**
     * Adds or updates model item in the models storage
     *
     * @param Currency | CurrencyRateProvider $toUpdate
     * @param string $storage
     * @return bool
     */
    public static function updateStorage($toUpdate, $storage)
    {
        $method = self::getMethod($toUpdate);
        $current = CurrenciesModule::module()->$method(true);
        $current[$toUpdate->name] = $toUpdate->attributes;
        return self::generateStorage($current, $storage, $toUpdate::className());
    }

    /**
     * @param array $data
     * @param string $storage
     * @param string $className
     * @return bool
     */
    public static function generateStorage($data = [], $storage, $className)
    {
        $storage = Yii::getAlias($storage);
        $writer = new ApplicationConfigWriter([
            'filename' => $storage,
        ]);
        $data = self::reformatData($data);
        $writer->addValues($data);
        $className::invalidateCache();
        return $writer->commit();
    }

    /**
     * @param $data
     * @return array
     */
    public static function reformatData($data)
    {
        foreach ($data as $i => $item) {
            if ($i !== $item['name']) {
                $data[$item['name']] = $item;
                unset($data[$i]);
            }
        }
        return $data;
    }

    /**
     * Returns CurrenciesModule associated method for given model
     *
     * @param $model
     * @return string
     */
    public static function getMethod($model)
    {
        $method = '';
        if ($model instanceof Currency) {
            $method = 'getCurrencies';
        } else if ($model instanceof CurrencyRateProvider) {
            $method = 'getProviders';
        } else {
            throw new InvalidParamException(
                Yii::t(
                    'dotplant.currencies',
                    'Model must be a valid instance of Currency or CurrencyRateProvider. "' . get_class($model) . '" given.'
                )
            );
        }
        if (true === method_exists(CurrenciesModule::className(), $method)) {
            return $method;
        } else {
            throw new InvalidCallException(
                Yii::t(
                    'dotplant.currencies',
                    "Method {$method} not exists in the CurrenciesModule!"
                )
            );
        }
    }
}