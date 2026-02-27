<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Medas\Console\ConsolePackage;
use Medas\Core\{AsSingleton, BasePackage};

class ApiKeysPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            ConsolePackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
