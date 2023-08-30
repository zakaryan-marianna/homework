<?php

namespace App\Objects;

use App\Contracts\CurrencyManager;
use App\Enums\OperationType;
use App\Enums\UserType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class Operation
{
    /**
     * Instance of the Currency Manager.
     *
     * @var CurrencyManager
     */
    private CurrencyManager $currencyManager;

    /**
     * Commission for the operation.
     *
     * @var float
     */
    private float $commission;

    /**
     * Week start date.
     * Keeping this as a parameter to not generate with every call.
     *
     * @var string
     */
    public readonly string $weekStartDate;

    public function __construct(
        public readonly Carbon        $date,
        public readonly int           $userId,
        public readonly UserType      $userType,
        public readonly OperationType $operationType,
        public readonly float         $amount,
        public readonly string        $currency,
    )
    {
        // Created separate Objects namespace because this isn't a DB entity, otherwise it's better to keep as a Model.
        $this->currencyManager = App::make(CurrencyManager::class);

        $this->weekStartDate = $this->date->startOfWeek()->format('Y-m-d');
    }

    /**
     * Get amount in base currency.
     *
     * @return float
     */
    public function getAmountInBaseCurrency(): float
    {
        return $this->currencyManager->exchangeToBaseCurrency($this->currency, $this->amount);
    }

    /**
     * Set commission in base currency.
     * It will be cast to local currency.
     *
     * @param float $commission
     * @return void
     */
    public function setCommissionInBaseCurrency(float $commission): void
    {
        $commissionInLocalCurrency = $this->currencyManager->exchangeToLocalCurrency($this->currency, $commission);
        $this->commission = $commissionInLocalCurrency;
    }

    /**
     * Get formatted commission.
     *
     * @return string
     */
    public function getFormattedCommission(): string
    {
        return $this->currencyManager->getFormattedAmount($this->currency, $this->commission);
    }
}
