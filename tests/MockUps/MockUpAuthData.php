<?php

declare(strict_types=1);

namespace Medas\ApiKeysTest\MockUps;

use Medas\Core\Interfaces\AuthenticationData;

class MockUpAuthData implements AuthenticationData
{
    public function __construct(
        public int    $id,
        public string $name,
    )
    {
    }

    public function getUserId(): int
    {
        return $this->id;
    }
}
