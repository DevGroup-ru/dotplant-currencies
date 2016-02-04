<?php

namespace DotPlant\Currencies\models;

use DotPlant\Currencies\CurrenciesModule;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Yii;
use yii\helpers\Json;

/**
 * Model for storing name and params of Currency Rate Providers
 * Currency rate provider class should be an implementation of Swap\ProviderInterface
 *
 * @property string $name
 * @property string $class_name
 * @property string $params
 */
class CurrencyRateProvider extends BaseFileModel
{
    public $name;
    public $class_name;
    public $params;

    protected static $cacheKey;
    protected static $storage;
    protected static $models = [];

    public function init()
    {
        parent::init();
        self::$cacheKey = CurrenciesModule::module()->providersCacheKey;
        self::$storage = Yii::getAlias(CurrenciesModule::module()->providersStorage);
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
            [['params'], 'string'],
            [['name', 'class_name'], 'required'],
            [['name', 'class_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('dotplant.currencies', 'Name'),
            'class_name' => Yii::t('dotplant.currencies', 'Class Name'),
            'params' => Yii::t('dotplant.currencies', 'Params'),
        ];
    }

    /**
     * Returns Swap provider instance for currency rate gathering
     * @param HttpAdapterInterface $httpAdapter
     * @return \Swap\ProviderInterface
     */
    public function getImplementationInstance(HttpAdapterInterface $httpAdapter)
    {
        $reflection_class = new \ReflectionClass($this->class_name);
        $params = ['httpAdapter' => $httpAdapter];
        if (!empty($this->params)) {
            $additionalParams = Json::decode($this->params);
            foreach ($reflection_class->getMethod('__construct')->getParameters() as $parameter) {
                if ($parameter->name === 'httpAdapter') {
                    continue;
                }
                $params[$parameter->name] = isset($additionalParams[$parameter->name])
                    ? $additionalParams[$parameter->name]
                    : null;
            }
            $additionalParams = null;
        }
        return $reflection_class->newInstanceArgs($params);
    }
}
