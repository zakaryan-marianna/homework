<?php

namespace App\Rules;

use App\Enums\OperationType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class OperationTypeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $case = OperationType::tryFrom($value);

        if ($case === null) {
            $fail(':attribute must be a valid user type');
        }
    }
}
