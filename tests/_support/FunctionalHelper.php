<?php
namespace Codeception\Module;

use DotPlant\Currencies\models\Currency;
use Yii;

class FunctionalHelper extends \Codeception\Module
{
    private static $testStorage = '@vendor/dotplant/currencies/tests/_output/ts.php';

    public static function testStorage()
    {
        return Yii::getAlias(self::$testStorage);
    }

    public static function flushStorage($fn = '')
    {
        if (true === empty($fn)) {
            $fn = Yii::getAlias(self::$testStorage);
        }
        if (true === file_exists($fn)) {
            unlink($fn);
        }
    }

    public static function flushCurrencyStorage()
    {
        $storage = (new Currency())->getStorage();
        self::flushStorage($storage);
    }
}
