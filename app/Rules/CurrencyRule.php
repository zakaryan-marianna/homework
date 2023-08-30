<?php

namespace App\Rules;

use App\Contracts\CurrencyManager;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\App;
use Illuminate\Translation\PotentiallyTranslatedString;

class CurrencyRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var CurrencyManager $currencyManager */
        $currencyManager = App::make(CurrencyManager::class);
        $availableCurrencies = $currencyManager->getAvailableCurrencies();
        if (!in_array($value, $availableCurrencies)) {
            $fail(":attribute is invalid");
        }
    }
}
