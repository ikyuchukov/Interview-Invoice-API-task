<?php
declare(strict_types=1);

namespace App\DTO;

class Customer
{
    private string $name;
    private string $balance;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getBalance(): string
    {
        return $this->balance;
    }

    /**
     * @param string $balance
     *
     * @return self
     */
    public function setBalance(string $balance): self
    {
        $this->balance = $balance;
        return $this;
    }
}
