<?php
namespace DotPlant\Currencies\models;

use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\events\FileModelEvent;
use DotPlant\Currencies\helpers\CurrencyStorageHelper;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\Model;

class BaseFileModel extends Model
{
    public $name;

    protected static $cacheKey;
    protected static $storage;

    protected static $models = [];

    const SCENARIO_NEW = 'new-dp-currencies-item';
    const EVENT_BEFORE_SAVE = 'dp-currency-item-before-save';
    const EVENT_BEFORE_UPDATE = 'dp-currency-item-before-update';

    protected $defaults = [];

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[static::SCENARIO_NEW] = array_keys($this->attributeLabels());
        return $scenarios;
    }

    /**
     * Find all CurrencyRateProvider and placing them into cache
     *
     * @return array Currency
     */
    public static function findAll()
    {
        if (0 == count(static::$models)) {
            $models = Yii::$app->cache->get(static::$cacheKey);
            if (false === $models) {
                $models = [];
                $method = self::getMethod();
                $items = CurrenciesModule::module()->$method(true);
                foreach ($items as $item) {
                    $model = new static;
                    $model->setDefaults();
                    $model->setAttributes($item);
                    $models[$model->name] = $model;
                }
                if (false === empty($models)) {
                    Yii::$app->cache->set(
                        static::$cacheKey,
                        $models,
                        86400
                    );
                }
            }
            static::$models = $models;
        }
        return static::$models;
    }

    /**
     * Returns CurrenciesModule associated method for given model
     *
     * @return string
     */
    private static function getMethod()
    {
        $method = '';
        $model = new static;
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

    /**
     * Returns model by given name
     *
     * @param $name
     * @return mixed
     */
    public static function getByName($name)
    {
        static::findAll();
        if (false === isset(static::$models[$name])) {
            return null;
        }
        return static::$models[$name];
    }

    /**
     * Custom implementation of ActiveRecord::isNewRecord() for file models
     *
     * @return bool
     */
    public function isNewItem()
    {
        self::findAll();
        return (true === empty($this->errors)) && in_array($this->name, array_keys(static::$models));
    }

    /**
     * Currency name validator. Currency name must be unique
     *
     * @param $attribute
     */
    public function checkNameIsUnique($attribute)
    {
        static::findAll();
        if (true === in_array($this->$attribute, array_keys(static::$models))) {
            $this->addError(
                $attribute,
                Yii::t('dotplant.currencies', 'Name {name} already in use!', ['name' => $this->$attribute])
            );
        }
    }

    /**
     * Invalidates the currencies file cache
     */
    public static function invalidateCache()
    {
        static::$models = [];
        Yii::$app->cache->delete(static::$cacheKey);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate(
                Yii::getAlias(static::$storage),
                true);
        }
    }

    /**
     * Custom implementation of ActiveRecord::loadDefaultValues() for file models
     */
    public function setDefaults()
    {
        $this->setAttributes($this->defaults);
    }

    /**
     * Saves model data
     *
     * @return bool
     */
    public function save()
    {
        $this->beforeSave($this->isNewItem());
        return CurrencyStorageHelper::updateStorage($this, static::$storage);
    }

    /**
     * Custom implementation of ActiveRecord::beforeSave() for file model
     *
     * @param $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $event = new FileModelEvent;
        $this->trigger($insert ? self::EVENT_BEFORE_SAVE : self::EVENT_BEFORE_UPDATE, $event);
        return $event->isValid;
    }
}