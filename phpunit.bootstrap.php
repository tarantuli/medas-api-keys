<?php

declare(strict_types=1);

use Medas\ApiKeys\ApiKeysPackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        ApiKeysPackage::instance(),
    ]);

    return $config;
});
