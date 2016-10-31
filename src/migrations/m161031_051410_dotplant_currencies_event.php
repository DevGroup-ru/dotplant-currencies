<?php

use DevGroup\EventsSystem\models\Event;
use DevGroup\EventsSystem\models\EventGroup;
use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\events\AfterUserCurrencyChangeEvent;
use yii\db\Migration;

class m161031_051410_dotplant_currencies_event extends Migration
{
    public function up()
    {
        $this->insert(
            EventGroup::tableName(),
            [
                'name' => 'Currencies',
                'owner_class_name' => CurrenciesModule::class,
            ]
        );
        $egId = $this->db->lastInsertID;
        $this->batchInsert(
            Event::tableName(),
            ['event_group_id', 'name', 'event_class_name', 'execution_point'],
            [
                [
                    $egId,
                    'After user currency change',
                    AfterUserCurrencyChangeEvent::class,
                    CurrenciesModule::AFTER_USER_CURRENCY_CHANGE
                ]
            ]
        );
    }

    public function down()
    {
        $this->delete(
            Event::tableName(),
            [
                'event_class_name' => [
                    AfterUserCurrencyChangeEvent::class,
                ]
            ]
        );
        $this->delete(EventGroup::tableName(), ['owner_class_name' => CurrenciesModule::class]);
    }
}
