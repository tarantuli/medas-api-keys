<?php

declare(strict_types=1);

namespace Medas\ApiKeysTest\Functional;

use Medas\ApiKeys\{ConfigOptions\DefaultKeyLength, KeyCreator};
use Medas\ConfigOptions\OptionController;
use PHPUnit\Framework\TestCase;

class KeyCreatorTest extends TestCase
{
    public function testCreation(): void
    {
        $keyCreator = service(KeyCreator::class);
        $defaultLength = service(OptionController::class)->getValue(service(DefaultKeyLength::class));

        // Create two keys
        $key1 = $keyCreator->create();
        $key2 = $keyCreator->create();

        self::assertEquals($defaultLength, strlen($key1));
        self::assertNotEquals($key2, $key1);
    }

    public function testOddLength(): void
    {
        $keyCreator = service(KeyCreator::class);
        $key = $keyCreator->create(17);

        self::assertEquals(17, strlen($key));
    }
}
