<?php

namespace DotPlant\Currencies\events;

use yii\base\Event;

/**
 * Class FileModelEvent is an event triggered when any subclass instance of BaseFileModel updates or adds
 *
 * @package DotPlant\Currencies\events
 */
class FileModelEvent extends Event
{
    /** @var bool  */
    public $isValid = true;
}