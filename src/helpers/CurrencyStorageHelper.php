<?php
namespace DotPlant\Currencies\helpers;

use DevGroup\ExtensionsManager\helpers\ApplicationConfigWriter;
use DotPlant\Currencies\models\BaseFileModel;
use DotPlant\Currencies\models\CurrencyRateProvider;
use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\models\Currency;
use yii\base\InvalidParamException;
use yii\base\Object;
use Yii;

class CurrencyStorageHelper extends Object
{
    /**
     * Removes given model item from items storage
     *
     * @param Currency | CurrencyRateProvider $toRemove
     * @throws InvalidParamException
     * @return bool
     */
    public static function removeFromStorage($toRemove)
    {
        if (false === ($toRemove instanceof BaseFileModel)) {
            throw new InvalidParamException(
                Yii::t('dotplant.currencies', 'Method {method} expects instance of {model}. {type} given',
                    ['method' => __METHOD__, 'model' => BaseFileModel::className(), 'type' => gettype($toRemove)])
            );
        }
        $current = CurrenciesModule::module()->getData($toRemove::className(), true, false);
        unset($current[$toRemove->name]);
        return self::generateStorage($current, $toRemove->getStorage(), $toRemove::className());
    }

    /**
     * Adds or updates model item in the models storage
     *
     * @param Currency | CurrencyRateProvider $toUpdate
     * @throws InvalidParamException
     * @return bool
     */
    public static function updateStorage($toUpdate)
    {
        if (false === ($toUpdate instanceof BaseFileModel)) {
            throw new InvalidParamException(
                Yii::t('dotplant.currencies', 'Method {method} expects instance of {model}. {type} given',
                    ['method' => __METHOD__, 'model' => BaseFileModel::className(), 'type' => gettype($toUpdate)])
            );
        }
        $current = CurrenciesModule::module()->getData($toUpdate::className(), true, false);
        $current[$toUpdate->name] = $toUpdate->attributes;
        return self::generateStorage($current, $toUpdate->getStorage(), $toUpdate::className());
    }

    /**
     * @param array $data
     * @param string $storage
     * @param string $className
     * @throws InvalidParamException
     * @return bool
     */
    public static function generateStorage($data = [], $storage, $className)
    {
        if (false === is_array($data)) {
            throw new InvalidParamException(
                Yii::t('dotplant.currencies', 'Method {method} expects array as first argument. {type} given',
                    ['method' => __METHOD__, 'type' => gettype($data)])
            );
        }
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
}