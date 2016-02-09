<?php
namespace DotPlant\Currencies\models;

use DotPlant\Currencies\helpers\CurrencyStorageHelper;
use DotPlant\Currencies\events\FileModelEvent;
use DotPlant\Currencies\CurrenciesModule;
use yii\base\Model;
use Yii;

class BaseFileModel extends Model
{
    public $name;

    protected static $cacheKey;
    protected static $storage = '';

    protected static $models = [];

    const SCENARIO_NEW = 'new-dp-currencies-item';
    const EVENT_BEFORE_SAVE = 'dp-currency-item-before-save';
    const EVENT_BEFORE_UPDATE = 'dp-currency-item-before-update';
    const EVENT_BEFORE_DELETE = 'dp-currency-item-before-delete';
    const EVENT_AFTER_DELETE = 'dp-currency-item-after-delete';

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
                $items = CurrenciesModule::module()->getData(static::className(), true, false);
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
        $new = false;
        if (true === in_array($this->name, array_keys(static::$models))) {
            if (false === empty($this->errors)) {
                $new = true;
            }
        } else {
            $new = true;
        }
        return $new;
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
     * Removes the model data from storage
     *
     * @return bool
     */
    public function delete()
    {
        $result = false;
        if (true === $this->beforeDelete()) {
            $result = CurrencyStorageHelper::removeFromStorage($this, static::$storage);
            $this->afterDelete();
        }
        return $result;


    }

    /**
     * This method is invoked before deleting a model data.
     *
     * @return bool
     */
    public function beforeDelete()
    {
        $event = new FileModelEvent;
        $this->trigger(self::EVENT_BEFORE_DELETE, $event);
        return $event->isValid;
    }

    /**
     * This method is invoked after deleting a model data.
     */
    public function afterDelete()
    {
        $this->trigger(self::EVENT_AFTER_DELETE);
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
     * Returns model storage
     *
     * @return string
     */
    public function getStorage()
    {
        return static::$storage;
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