<?php
declare(strict_types=1);

namespace App\Validation;

use Symfony\Component\Validator\Constraints\Currency as CurrencyConstraint;

class CurrencyValidationConstrain
{
    public function __construct(
        private CurrencyConstraint $currencyConstrain,
    ) {
    }

    public function getConstraint(): CurrencyConstraint
    {
        return $this->currencyConstrain;
    }
}
