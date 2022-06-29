<?php
declare(strict_types=1);

namespace App\DTO;

class Transaction
{
    public const TYPE_INVOICE = 1;
    public const TYPE_CREDIT = 2;
    public const TYPE_DEBIT = 3;

    private string $customer;
    private string $vat;
    private string $documentId;
    private int $type;
    private ?string $parentDocumentId;
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

    public function getVat(): string
    {
        return $this->vat;
    }

    public function setVat(string $vat): self
    {
        $this->vat = $vat;
        return $this;
    }

    public function getDocumentId(): string
    {
        return $this->documentId;
    }

    public function setDocumentId(string $documentId): self
    {
        $this->documentId = $documentId;
        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getParentDocumentId(): ?string
    {
        return $this->parentDocumentId;
    }

    public function setParentDocumentId(?string $parentDocumentId = null): self
    {
        $this->parentDocumentId = $parentDocumentId;
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
