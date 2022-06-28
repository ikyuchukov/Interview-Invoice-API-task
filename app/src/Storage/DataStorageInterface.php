<?php

namespace App\Storage;

interface DataStorageInterface
{
    public function set($key, $object);
    public function delete($key);
    public function update($key, $object);
    public function add($key, $object);
    public function get($key);
    public function addWithAssociativeKey($key, string $associativeKey, $object);
}
