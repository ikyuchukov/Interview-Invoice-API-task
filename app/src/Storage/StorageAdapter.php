<?php
declare(strict_types=1);

namespace App\Storage;

class StorageAdapter implements DataStorageInterface
{
    public const REPOSITORY_EXCHANGE_RATE = 'exchange_rate';
    public const REPOSITORY_DEFAULT_CURRENCY = 'default_currency';
    public const REPOSITORY_TRANSACTION = 'transaction';

    public function __construct(
        private InMemoryStorage $dataStorage
    ) {
    }

    public function set($key, $object): void
    {
        $this->dataStorage->set($key, $object);
    }

    public function delete($key): void
    {
        $this->dataStorage->delete($key);
    }

    public function update($key, $object): void
    {
        $this->dataStorage->update($key, $object);
    }

    public function add($key, $object): void
    {
        $this->dataStorage->add($key, $object);
    }

    public function get($key): mixed
    {
        return $this->dataStorage->get($key);
    }

    public function addWithAssociativeKey($key, string $associativeKey, $object): void
    {
        $this->dataStorage->addWithAssociativeKey($key, $associativeKey, $object);
    }

}
