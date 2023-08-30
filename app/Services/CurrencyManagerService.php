<?php

namespace App\Services;

use App\Contracts\CurrencyManager;
use App\Exceptions\ExchangeRateApiDownException;
use App\Exceptions\InvalidCurrencyException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class CurrencyManagerService implements CurrencyManager
{
    /**
     * The base currency.
     *
     * @var string
     */
    private string $baseCurrency;

    /**
     * All supported currencies
     *
     * @var array
     */
    private array $currencies;

    /**
     * Currency exchange rates.
     *
     * @var array
     */
    private array $exchangeRates;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->baseCurrency = config('payments.base_currency');
        $this->currencies = config('payments.currencies');
    }

    /**
     * Exchange amount to base currency from local currency.
     *
     * @param string $currency
     * @param float $amount
     * @return float
     */
    public function exchangeToBaseCurrency(string $currency, float $amount): float
    {
        $exchangeRate = $this->getExchangeRate($currency);

        return $amount / $exchangeRate;
    }

    /**
     * Exchange amount to local currency from base currency.
     *
     * @param string $currency
     * @param float $amount
     * @return float
     */
    public function exchangeToLocalCurrency(string $currency, float $amount): float
    {
        $exchangeRate = $this->getExchangeRate($currency);

        return $amount * $exchangeRate;
    }

    /**
     * Get ceil formatted amount for currency.
     *
     * @param string $currency
     * @param float $amount
     * @return string
     */
    public function getFormattedAmount(string $currency, float $amount): string
    {
        $this->validateCurrency($currency);
        $decimalPoints = $this->currencies[$currency]['decimal_points'];
        $multiplier = pow(10, $decimalPoints);
        $ceilNumber = ceil($multiplier * $amount) / $multiplier;

        return number_format($ceilNumber, $decimalPoints, '.', '');
    }

    /**
     * Get all available currencies.
     *
     * @return array
     */
    public function getAvailableCurrencies(): array
    {
        return array_keys($this->currencies);
    }

    /**
     * Get currency exchange rate.
     *
     * @param string $currency
     * @return float
     */
    private function getExchangeRate(string $currency): float
    {
        $this->validateCurrency($currency);
        if ($currency === $this->baseCurrency) {
            return 1;
        }
        if (!isset($this->exchangeRates)) {
            // Loading exchange rates here to keep the requirement #7.
            // Ideally, this should not be here, and exchange rates must be available locally in some table of the DB
            //     and can be updated via CRON.
            $this->loadExchangeRates();
        }
        return $this->exchangeRates[$currency];
    }

    /**
     * Validate currency.
     *
     * @param $currency
     * @return void
     */
    private function validateCurrency($currency): void
    {
        $availableCurrencies = $this->getAvailableCurrencies();
        if (!in_array($currency, $availableCurrencies)) {
            throw new InvalidCurrencyException("Currency '$currency' is invalid.");
        }
    }

    /**
     * Load all exchange rates.
     *
     * @return void
     */
    private function loadExchangeRates(): void
    {
        try {
            $url = config('payments.exchange_rates_source');
            $response = Http::retry(2, 500)->get($url)->throw();
        } catch (RequestException $exception) {
            // Log the exception
            report($exception);
            throw new ExchangeRateApiDownException('Exchange Rate API is down, try again later.');
        }
        $rates = $response->json('rates');
        $availableCurrencies = $this->getAvailableCurrencies();
        $ratesOfAvailableCurrencies = Arr::only($rates, $availableCurrencies);
        $this->exchangeRates = $ratesOfAvailableCurrencies;
    }
}
