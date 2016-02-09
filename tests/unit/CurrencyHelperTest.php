<?php
use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\models\Currency;
use DotPlant\Currencies\helpers\CurrencyHelper;
use Codeception\Module\FunctionalHelper;
use DotPlant\Currencies\helpers\CurrencyStorageHelper;

class CurrencyHelperTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testGetMainCurrencyCorrect()
    {
        $data = [
            [
                'name' => 'US Dollar',
                'iso_code' => 'USD',
                'convert_nominal' => 1,
                'convert_rate' => 62.83,
                'sort_order' => 1,
                'format_string' => '$ #',
                'thousands_sep' => '.',
                'dec_point' => ',',
                'is_main' => 0,
            ],
            'Euro' => [
                'name' => 'Euro',
                'iso_code' => 'EUR',
                'convert_rate' => 71.32,
                'format_string' => '&euro; #',
                'is_main' => 1,
            ],
        ];
        $storage = (new Currency())->getStorage();
        FunctionalHelper::flushStorage($storage);
        $this->assertFileNotExists($storage);
        CurrencyStorageHelper::generateStorage($data, $storage, Currency::className());
        $this->assertFileExists($storage);
        $c = CurrencyHelper::getMainCurrency();
        $this->assertInstanceOf(Currency::className(), $c);
        FunctionalHelper::flushCurrencyStorage();
    }


    public function testFindCurrencyByIso()
    {
        $data = [
            [
                'name' => 'US Dollar',
                'iso_code' => 'USD',
                'convert_nominal' => 1,
                'convert_rate' => 80,
                'sort_order' => 1,
                'format_string' => '$ #',
                'thousands_sep' => '.',
                'dec_point' => ',',
                'is_main' => 0,
                'intl_formatting' => 1,
            ],
            'Euro' => [
                'name' => 'Euro',
                'iso_code' => 'EUR',
                'convert_rate' => 90,
                'format_string' => '&euro; #',
                'is_main' => 1,
            ],
        ];
        $storage = (new Currency())->getStorage();
        FunctionalHelper::flushStorage($storage);
        $this->assertFileNotExists($storage);
        CurrencyStorageHelper::generateStorage($data, $storage, Currency::className());
        $c = CurrencyHelper::findCurrencyByIso('USD');
        $this->assertEquals('US Dollar', $c->name);
        return $c;
    }

    /**
     * @depends testFindCurrencyByIso
     * @param Currency $c
     */
    public function testGetCurrencySymbol(Currency $c)
    {
        if (false === ($c instanceof Currency)) {
            $this->markTestSkipped('We got something like not Currency');
        }
        $symbol = CurrencyHelper::getCurrencySymbol($c);
        $this->assertEquals('$', $symbol);
    }

    /**
     * @depends testFindCurrencyByIso
     * @param Currency $c
     */
    public function testFormat(Currency $c)
    {
        if (false === ($c instanceof Currency)) {
            $this->markTestSkipped('We got something like not Currency');
        }
        $formatted = CurrencyHelper::format(100, $c);
        $this->assertEquals('100,00 $', $formatted);
    }
}