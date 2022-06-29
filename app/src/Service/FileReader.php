<?php
declare(strict_types=1);

namespace App\Service;

/**
 * This is just to be able to mock related services
 * normally this will be a full-fledged service/library, but this
 * will suffice in this case
 */
class FileReader
{
    public function readFileToString(string $filePath): string
    {
        return file_get_contents($filePath);
    }
}
