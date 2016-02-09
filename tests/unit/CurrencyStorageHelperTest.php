<?php
use DotPlant\Currencies\helpers\CurrencyStorageHelper;
use DotPlant\Currencies\models\Currency;
use Codeception\Module\FunctionalHelper;
use DotPlant\Currencies\CurrenciesModule;

class CurrencyStorageHelperTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
        FunctionalHelper::flushStorage();
    }

    public function testGenerateStorageEmptyData()
    {
        CurrencyStorageHelper::generateStorage([], FunctionalHelper::testStorage(), Currency::className());
        $this->assertFileExists(FunctionalHelper::testStorage());
        $a = [111,1];
        if (true === file_exists(FunctionalHelper::testStorage())) {
            $a = include FunctionalHelper::testStorage();
        }
        $this->assertEmpty($a);
    }

    public function testGenerateStorageTestData()
    {
        $data = CurrenciesModule::module()->getDefaults(Currency::className());
        CurrencyStorageHelper::generateStorage($data, FunctionalHelper::testStorage(), Currency::className());
        $this->assertFileExists(FunctionalHelper::testStorage());
        if (true === file_exists(FunctionalHelper::testStorage())) {
            $a = include FunctionalHelper::testStorage();
            $this->assertCount(3, $a);
        }
    }

    /**
     * @expectedException     \yii\base\InvalidParamException
     */
    public function testGenerateStorageInvalidData()
    {
        CurrencyStorageHelper::generateStorage('bad.data', FunctionalHelper::testStorage(), Currency::className());
    }

    public function testUpdateStorage()
    {
        $a = [
            'name' => 'Newerlands peso',
            'iso_code' => 'NPS',
            'is_main' => 1,
            'format_string' => '# pps.',
            'intl_formatting' => 0,
        ];
        $c = new Currency();
        $c->setDefaults();
        $c->setAttributes($a);
        FunctionalHelper::flushStorage($c->getStorage());
        CurrencyStorageHelper::updateStorage($c);
        $this->assertFileExists($c->getStorage());
        $curr = [];
        if (true === file_exists($c->getStorage())) {
            $curr = include $c->getStorage();
        }
        $this->assertNotEmpty($curr);
        $this->assertCount(1, $curr);
        $this->assertArrayHasKey($a['name'], $curr);
        FunctionalHelper::flushStorage($c->getStorage());
    }

    /**
     * @expectedException     \yii\base\InvalidParamException
     */
    public function testUpdateStorageWrongModel()
    {
        CurrencyStorageHelper::updateStorage([]);
    }

    public function testGenerateStorageDefaultData()
    {
        $storage = (new Currency())->getStorage();
        FunctionalHelper::flushStorage($storage);
        $this->assertFileNotExists($storage);
        CurrenciesModule::module()->getData(Currency::className());
        $this->assertFileExists($storage);
        $a = [];
        if (true === file_exists($storage)) {
            $a = include $storage;
        }
        $this->assertEquals($a, CurrenciesModule::module()->getDefaults(Currency::className()));
        return $storage;
    }

    /**
     * @depends testGenerateStorageDefaultData
     * @param $storage
     */
    public function testRemoveFromStorage($storage)
    {
        if (false === file_exists($storage)) {
            $this->markTestSkipped();
        }
        $c = new Currency();
        $c->name = 'Ruble';
        $a = [];
        CurrencyStorageHelper::removeFromStorage($c);
        if (true === file_exists($storage)) {
            $a = include $storage;
        }
        $this->assertCount(2, $a);
        FunctionalHelper::flushStorage($storage);
    }

    public function testGenerateWithReformat()
    {
        $data = [
            [
                'name' => 'Ruble',
                'iso_code' => 'RUB',
                'is_main' => 1,
                'format_string' => '# руб.',
                'intl_formatting' => 0,
            ],
            [
                'name' => 'US Dollar',
                'iso_code' => 'USD',
                'convert_nominal' => 1,
                'convert_rate' => 62.835299999999997,
                'sort_order' => 1,
                'format_string' => '$ #',
                'thousands_sep' => '.',
                'dec_point' => ',',
            ],
            'Euro' => [
                'name' => 'Euro',
                'iso_code' => 'EUR',
                'convert_rate' => 71.324299999999994,
                'format_string' => '&euro; #',
            ],
        ];
        $storage = (new Currency())->getStorage();
        FunctionalHelper::flushStorage($storage);
        CurrencyStorageHelper::generateStorage($data, $storage, Currency::className());
        $this->assertFileExists($storage);
        $a = [];
        if (true === file_exists($storage)) {
            $a = include $storage;
        }
        $this->assertCount(3, $a);
        FunctionalHelper::flushStorage($storage);
    }
    /**
     * @expectedException     \yii\base\InvalidParamException
     */
    public function testRemoveFromStorageWrongModel()
    {
        CurrencyStorageHelper::removeFromStorage('im a Currency model, btc!');
    }
}