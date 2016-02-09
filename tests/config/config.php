<?php
/**
 * Application configuration shared by all test types
 */
return [
    'language' => 'ru',
    'controllerMap' => [
    ],
    'components' => [
        'urlManager' => [
            'showScriptName' => false,
        ],
    ],
    'modules' => [
        'currencies' => [
            'class' => 'DotPlant\\Currencies\\CurrenciesModule',
            'currenciesStorage' => '@vendor/dotplant/currencies/tests/_output/dpts-currency.php',
            'currenciesCacheKey' => 'dotplant.currencies.currenciesModels',
            'providersStorage' => '@vendor/dotplant/currencies/tests/_output/dpts-providers.php',
            'providersCacheKey' => 'dotplant.currencies.CurrencyRateProvidersModels',
        ],
    ]
];