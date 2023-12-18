<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Medas\Core\Attributes\{ConfigValue, Service};

#[Service]
readonly class KeyCreator
{
    public function __construct(
        #[ConfigValue(ConfigOptions\DefaultKeyLength::class)]
        private int $defaultKeyLength,
    )
    {
    }

    public function create(int $length = null): string
    {
        return bin2hex(random_bytes(($length ?? $this->defaultKeyLength) / 2));
    }
}
