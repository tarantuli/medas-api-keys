<?php

declare(strict_types=1);

namespace Medas\ApiKeysTest\Functional;

use Medas\ApiKeys\KeyStoreManager;
use Medas\JsonStorage\StorageFile;
use PHPUnit\Framework\TestCase;

class KeyStoreTest extends TestCase
{
    public function testCreate(): void
    {
        $storagePath = __DIR__ . '/../Storage/test-api-keys.json';

        if (file_exists($storagePath)) {
            unlink($storagePath);
        }

        $store = service(KeyStoreManager::class)->get();

        self::assertInstanceOf(StorageFile::class, $store);
    }
}
