<?php
declare(strict_types=1);

namespace App\Storage;

use App\Storage\Exception\DataKeyNotFoundException;

class InMemoryStorage implements DataStorageInterface
{
    public function __construct(
        private array $memory = []
    ) {
    }

    public function set($key, $object): void
    {
        $this->memory[$key] = $object;
    }

    /**
     * @throws DataKeyNotFoundException
     */
    public function delete($key): void
    {
        if (isset($this->memory[$key])) {
            unset($this->memory[$key]);
        } else {
           throw new DataKeyNotFoundException(sprintf('Can not find key %s in memory', $key));
        }
    }

    /**
     * @throws DataKeyNotFoundException
     */
    public function update($key, $object): void
    {
        if (isset($this->memory[$key])) {
            $this->memory[$key] = $object;
        } else {
            throw new DataKeyNotFoundException(sprintf('Can not find key %s in memory', $key));
        }
    }

    /**
     * @throws DataKeyNotFoundException
     */
    public function add($key, $object): void
    {
        if (isset($this->memory[$key])) {
            $this->memory[$key][] = $object;
        } else {
            throw new DataKeyNotFoundException(sprintf('Can not find key %s in memory', $key));
        }
    }

    /**
     * @throws DataKeyNotFoundException
     */
    public function addWithAssociativeKey($key, string $associativeKey, $object): void
    {
        $this->memory[$key][$associativeKey] = $object;
        if (isset($this->memory[$key])) {
            $this->memory[$key][$associativeKey] = $object;
        } else {
            throw new DataKeyNotFoundException(sprintf('Can not find key %s in memory', $key));
        }
    }

    public function get($key): mixed
    {
        return $this->memory[$key] ?? null;
    }
}
