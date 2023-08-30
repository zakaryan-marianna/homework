<?php

namespace App\Contracts;

interface CurrencyManager
{
    /**
     * Exchange amount to base currency from local currency.
     *
     * @param string $currency
     * @param float $amount
     * @return float
     */
    public function exchangeToBaseCurrency(string $currency, float $amount): float;

    /**
     * Exchange amount to local currency from base currency.
     *
     * @param string $currency
     * @param float $amount
     * @return float
     */
    public function exchangeToLocalCurrency(string $currency, float $amount): float;

    /**
     * Get ceil formatted amount for currency.
     *
     * @param string $currency
     * @param float $amount
     * @return string
     */
    public function getFormattedAmount(string $currency, float $amount): string;

    /**
     * Get all available currencies.
     *
     * @return array
     */
    public function getAvailableCurrencies(): array;
}
