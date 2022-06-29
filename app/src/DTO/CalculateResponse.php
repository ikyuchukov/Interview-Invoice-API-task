<?php
declare(strict_types=1);

namespace App\DTO;

class CalculateResponse
{
    public string $currency;

    /**
     * @var Customer[]
     */
    public array $customers;

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return self
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return Customer[]
     */
    public function getCustomers(): array
    {
        return $this->customers;
    }

    /**
     * @param Customer[] $customers
     *
     * @return self
     */
    public function setCustomers(array $customers): self
    {
        $this->customers = $customers;
        return $this;
    }

    public function addCustomer(Customer $customer) : self
    {
        $this->customers[] = $customer;
        return $this;
    }
}
