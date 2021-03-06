<?php

namespace DotPlant\Currencies\components\swap\provider;

use Swap\Provider\AbstractProvider;
use \Swap\Exception\Exception;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;

class CbrFinanceProvider extends AbstractProvider
{
    const URL = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=%s';

    public function fetchRate(CurrencyPair $currencyPair)
    {
        $date = date("d/m/Y");

        $url = sprintf(self::URL, $date);

        $content = $this->httpAdapter->get($url)->getBody()->getContents();
        $cbr = new \SimpleXMLElement($content);

        $res = $cbr->xpath('/ValCurs/Valute[CharCode="' . $currencyPair->getBaseCurrency() . '"]');
        if (array_key_exists(0, $res)) {
            return new Rate(str_replace(',', '.', $res[0]->Value), new \DateTime());
        } else {
            throw new Exception('The currency is not supported');
        }
    }

}