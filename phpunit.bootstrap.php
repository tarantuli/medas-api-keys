<?php

declare(strict_types=1);

use Medas\ApiKeys\ApiKeysPackage;
use Medas\ConfigManager\ConfigManager;
use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\ConsolePrinter\ConsolePrinterPackage;
use Medas\JsonStorage\JsonStoragePackage;
use Medas\JsonStorage\StorageDirectory;
use Medas\StorageManager\StorageManager;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        ApiKeysPackage::instance(),
        ConfigManagerPackage::instance(),
        ConfigOptionsPackage::instance(),
        ConsolePrinterPackage::instance(),
        JsonStoragePackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)->addDirectory(__DIR__ . '/tests/MockUps/Settings');

service(StorageManager::class)->add(
    new StorageDirectory(__DIR__ . '/tests/Storage', 'test-storage')
);
