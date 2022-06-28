<?php
declare(strict_types=1);

namespace App\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ExchangeRateConstrains
{
    private const VALIDATION_REGEX_EXCHANGE_RATES = '%^[A-Z]{3}:\d*(.\d+)*$%';

    public function __construct(
        private array $exchangeRateConstrains = [new NotBlank(), new Regex(self::VALIDATION_REGEX_EXCHANGE_RATES)],
    ) {
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array
    {
        return $this->exchangeRateConstrains;
    }
}
