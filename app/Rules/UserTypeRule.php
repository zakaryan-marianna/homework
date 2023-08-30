<?php

namespace App\Rules;

use App\Enums\UserType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UserTypeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $case = UserType::tryFrom($value);

        if ($case === null) {
            $fail(':attribute must be a valid user type');
        }
    }
}
