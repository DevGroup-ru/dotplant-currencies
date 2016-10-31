<?php

namespace DotPlant\Currencies\events;

use DotPlant\Currencies\models\Currency;
use yii\base\Event;

/**
 * Class AfterUserCurrencyChangeEvent
 * @package DotPlant\Currencies\events
 */
class AfterUserCurrencyChangeEvent extends Event
{
    /**
     * @var Currency
     */
    public $oldUserCurrency;

    /**
     * @var Currency
     */
    public $newUserCurrency;
}
