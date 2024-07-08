<?php

declare(strict_types=1);

class Currency
{
    const CB_URL = "https://cbu.uz/uz/arkhiv-kursov-valyut/json/";
    private int $usdIndex = 0;

    public function exchange(float|int $uzs, $currencyName): float|int
    {
        $valyuta = $this->customCurrencies()[$currencyName];
        return $uzs / $valyuta;
    }

    public function getCurrencyInfo()
    {
        $currencyInfo = file_get_contents(self::CB_URL);
        return json_decode($currencyInfo);
    }

    public function customCurrencies(): array
    {
        $currencies        = (array) $this->getCurrencyInfo();
        $orderedCurrencies = [];
        foreach ($currencies as $currency) {
            
            $orderedCurrencies[$currency->Ccy] = $currency->Rate;
        }

        return $orderedCurrencies;
    }
}