<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Medas\Core\Attributes\{ConfigValue, Entrypoint, Service};

/**
 * This class creates random keys consisting of lowercase hexadecimal characters.
 */
#[Service]
readonly class KeyCreator
{
    public function __construct(
        #[ConfigValue(ConfigOptions\DefaultKeyLength::class)]
        private int $defaultKeyLength,
    )
    {
    }

    #[Entrypoint]
    public function create(int $length = null): string
    {
        $length ??= $this->defaultKeyLength;

        return substr(bin2hex(random_bytes((int) round($length / 2))), 0, $length);
    }
}
