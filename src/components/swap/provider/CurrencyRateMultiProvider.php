<?php

namespace DotPlant\Currencies\components\swap\provider;

use DotPlant\Currencies\models\CurrencyRateProvider;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Swap\Provider\AbstractProvider;
use Swap\Exception\Exception;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;
use Swap\Swap;
use Yii;

/**
 * Class MultiProvider
 * @package app\components\swap\provider
 */
class CurrencyRateMultiProvider extends AbstractProvider
{
    /**
     * @var string Main provider name
     */
    public $mainProvider;

    /**
     * @var string Second provider name
     */
    public $secondProvider;

    /**
     * @var int Critical rate difference in percent
     */
    public $criticalDifference;

    /**
     * @param HttpAdapterInterface $httpAdapter
     * @param $secondProvider
     * @param $mainProvider
     * @param int $criticalDifference
     */
    public function __construct(
        HttpAdapterInterface $httpAdapter,
        $secondProvider,
        $mainProvider,
        $criticalDifference = 20
    ) {
        parent::__construct($httpAdapter);
        $this->mainProvider = $mainProvider;
        $this->secondProvider = $secondProvider;
    }

    /**
     * @param CurrencyPair $currencyPair
     * @return Rate
     * @throws Exception
     */
    public function fetchRate(CurrencyPair $currencyPair)
    {
        $providers = [
            $this->mainProvider => CurrencyRateProvider::getByName($this->mainProvider),
            $this->secondProvider => CurrencyRateProvider::getByName($this->secondProvider),
        ];
        if (count($providers) !== 2) {
            throw new Exception('One of providers not found');
        }
        $rates = [];
        /** @var CurrencyRateProvider $provider */
        foreach ($providers as $name => $provider) {
            if (null === $provider) {
                throw new Exception("Provider \"{$name}\" not found!");
            }
            try {
                $providerHandler = $provider->getImplementationInstance($this->httpAdapter);
                if ($providerHandler !== null) {
                    $swap = new Swap($providerHandler);
                    $rate = $swap
                        ->quote($currencyPair->getBaseCurrency() . '/' . $currencyPair->getQuoteCurrency())
                        ->getValue();
                    $rates[] = floatval($rate);
                } else {
                    throw new Exception('Provider "' . $provider->name . '" not found');
                }
            } catch (\Exception $e) {
                throw new Exception('One or more currency providers did not return result');
            }
        }
        $min = min($rates);
        $max = max($rates);
        return new Rate(
            $max - $min >= $max * $this->criticalDifference / 100 ? $max : $rates[0],
            new \DateTime()
        );
    }
}
