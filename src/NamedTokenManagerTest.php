<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Medas\ApiKeysTest\MockUps\MockUpAuthData;
use PHPUnit\Framework\TestCase;

class NamedTokenManagerTest extends TestCase
{
    public function testBasicUsage(): void
    {
        $data = new MockUpAuthData(1, 'John Doe');
        $manager = service(NamedTokenManager::class);
        $token = $manager->create($data);

        self::assertEquals($data->getUserId(), $manager->userId($token));

        $manager->invalidate($token);
    }
}
