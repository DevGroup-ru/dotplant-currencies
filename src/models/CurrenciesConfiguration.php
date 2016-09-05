<?php

namespace DotPlant\Currencies\models;

use DotPlant\Currencies\controllers\CurrencyRateProvidersManageController;
use DevGroup\ExtensionsManager\models\BaseConfigurationModel;
use DotPlant\Currencies\controllers\CurrenciesManageController;
use DotPlant\Currencies\commands\CurrencyController;
use DotPlant\Currencies\CurrenciesModule;
use Yii;

class CurrenciesConfiguration extends BaseConfigurationModel
{
    /**
     * @inheritdoc
     */
    public function getModuleClassName()
    {
        return CurrenciesModule::className();
    }

    /**
     * Validation rules for this model
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['currenciesCacheKey', 'currenciesStorage', 'providersStorage', 'providersCacheKey'], 'required'],
            [['currenciesCacheKey', 'currenciesStorage', 'providersStorage', 'providersCacheKey'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'currenciesStorage' => Yii::t('dotplant.currencies', 'Currencies storage'),
            'providersStorage' => Yii::t('dotplant.currencies', 'Currency rate providers storage'),
            'currenciesCacheKey' => Yii::t('dotplant.currencies', 'Currencies cache key'),
            'providersCacheKey' => Yii::t('dotplant.currencies', 'Currency rate providers cache key'),
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    public function webApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
     *
     * @return array
     */
    public function consoleApplicationAttributes()
    {
        return [
            'controllerMap' => [
                'currencies' => CurrencyController::className(),
            ]
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web and console.
     *
     * @return array
     */
    public function commonApplicationAttributes()
    {
        return [
            'components' => [
                'i18n' => [
                    'translations' => [
                        'dotplant.currencies' => [
                            'class' => 'yii\i18n\PhpMessageSource',
                            'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'messages',
                        ]
                    ]
                ],
            ],
            'modules' => [
                'currencies' => [
                    'class' => CurrenciesModule::className(),
                    'currenciesStorage' => $this->currenciesStorage,
                    'currenciesCacheKey' => $this->currenciesCacheKey,
                    'providersStorage' => $this->providersStorage,
                    'providersCacheKey' => $this->providersCacheKey,
                ]
            ],
        ];
    }

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    public function appParams()
    {
        return [];
    }

    /**
     * Returns array of aliases that should be set in common config
     *
     * @return array
     */
    public function aliases()
    {
        return [
            '@DotPlant/Currencies' =>  realpath(dirname(__DIR__)),
        ];
    }
}
