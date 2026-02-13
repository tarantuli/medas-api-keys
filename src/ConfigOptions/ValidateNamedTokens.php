<?php

declare(strict_types=1);

namespace Medas\ApiKeys\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class ValidateNamedTokens implements ConfigOption
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
        return 'validate-named-tokens';
    }

    public function description(): string
    {
        return 'Whether to validate named tokens or not. Should be true under normal circumstances';
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
