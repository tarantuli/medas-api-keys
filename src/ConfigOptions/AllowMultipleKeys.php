<?php

declare(strict_types=1);

namespace Medas\ApiKeys\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class AllowMultipleKeys implements ConfigOption
{
    public function __construct(
        private ApiKeysConfigOptionsGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'allow-multiple-keys';
    }

    public function description(): string
    {
        return 'Whether to allow multiple (true) or at most one (false) key per name';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): true
    {
        return true;
    }
}
