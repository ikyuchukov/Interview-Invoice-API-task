<?php
declare(strict_types=1);

namespace App\DTO;

class CustomerInvoiceSummary
{
    private string $customer;
    private Money $total;

    public function getCustomer(): string
    {
        return $this->customer;
    }

    public function setCustomer(string $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function getTotal(): Money
    {
        return $this->total;
    }

    public function setTotal(Money $total): self
    {
        $this->total = $total;
        return $this;
    }
}
