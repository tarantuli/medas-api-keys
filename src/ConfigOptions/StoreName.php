<?php

declare(strict_types=1);

namespace Medas\ApiKeys\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class StoreName implements ConfigOption
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
        return 'store-name';
    }

    public function description(): string
    {
        return 'The name of the store where API keys are kept';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'api-keys';
    }
}
