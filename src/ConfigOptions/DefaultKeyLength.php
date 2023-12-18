<?php

declare(strict_types=1);

namespace Medas\ApiKeys\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class DefaultKeyLength implements ConfigOption
{
    public function __construct(
        private RootGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'default-key-length';
    }

    public function description(): string
    {
        return 'The default length of generated keys, should be a multiple of 2';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): int
    {
        return 64;
    }
}
