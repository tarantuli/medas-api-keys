<?php

declare(strict_types=1);

use Medas\ApiKeys\ApiKeysPackage;
use Medas\ConfigManager\{ConfigManager, ConfigManagerPackage};
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\ConsolePrinter\ConsolePrinterPackage;
use Medas\Events\EventsPackage;
use Medas\JsonStorage\{JsonStoragePackage, StorageDirectory};
use Medas\ObjectInstantiator\{ObjectInstantiator, ObjectInstantiatorPackage};
use Medas\ServiceManager\{ServiceConfig, ServiceManager};
use Medas\StorageManager\StorageManager;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig(ObjectInstantiator::class);

    $config->addPackages([
        ApiKeysPackage::instance(),
        ConfigManagerPackage::instance(),
        ConfigOptionsPackage::instance(),
        ConsolePrinterPackage::instance(),
        EventsPackage::instance(),
        JsonStoragePackage::instance(),
        ObjectInstantiatorPackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)->addDirectory(__DIR__ . '/tests/MockUps/Settings');
service(StorageManager::class)->add(new StorageDirectory(__DIR__ . '/tests/Storage', 'test-storage'));
