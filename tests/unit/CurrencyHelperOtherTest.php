<?php
use DotPlant\Currencies\models\Currency;
use Codeception\Module\FunctionalHelper;
use DotPlant\Currencies\helpers\CurrencyStorageHelper;
use DotPlant\Currencies\helpers\CurrencyHelper;

class CurrencyHelperOtherTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    /**
     * Using PHPUnit with static variables are painfully. Because of it this single test executes in different Class then others.
     */
    public function testGetMainCurrencyNotExists()
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
                'is_main' => 0,
            ],
        ];
        $storage = (new Currency())->getStorage();
        FunctionalHelper::flushStorage($storage);
        $this->assertFileNotExists($storage);
        CurrencyStorageHelper::generateStorage($data, $storage, Currency::className());
        $this->assertFileExists($storage);
        $c = CurrencyHelper::getMainCurrency();
        $this->assertNull($c);
        FunctionalHelper::flushCurrencyStorage();
    }

}