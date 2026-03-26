<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\StoreController;
use Medas\StorageManager\Structure\{Blueprint, Blueprint\Field, Blueprint\Index, Blueprint\Type};

#[Service]
readonly class KeyStoreManager
{
    private Store $store;

    public function __construct(
        private StorageManager  $storageManager,
        private StoreController $storeController,

        #[ConfigValue(ConfigOptions\AllowMultipleKeys::class)]
        private bool            $allowMultipleKeys,

        #[ConfigValue(ConfigOptions\StorageName::class)]
        private string|null     $storageName,

        #[ConfigValue(ConfigOptions\StoreName::class)]
        private string          $storeName,
    )
    {
    }

    public function storeKey(string $name, string $key, string|null $replaceHash = null): void
    {
        $keyHash = password_hash($key, PASSWORD_DEFAULT);

        if ($this->allowMultipleKeys) {
            // When replacing a hash (e.g., during rehash), delete the old record
            // first so stale hashes do not accumulate in the store.
            if ($replaceHash !== null) {
                $this->deleteHash($replaceHash);
            }

            $this->storeController->insert($this->get(), ['keyHash' => $keyHash, 'name' => $name]);
        }
        else {
            $this->storeController->upsert(
                $this->get(),
                ['keyHash' => $keyHash],
                ['name' => $name]
            );
        }
    }

    public function deleteHash(string $keyHash): void
    {
        $this->storeController->delete($this->get(), ['keyHash' => $keyHash]);
    }

    public function get(): Store
    {
        if (!isset($this->store)) {
            $storageController = $this->storageManager->controller($this->storageName);
            $store = $storageController->store($this->storeName);

            if (!$storageController->hasStore($store)) {
                $this->build($store);
            }

            $this->store = $store;
        }

        return $this->store;
    }

    private function build(Store $store): void
    {
        $blueprint = new Blueprint();

        $blueprint->name = $store->name();
        $nameField = new Field('name', Type::Text);
        $keyHashField = new Field('keyHash', Type::Text);
        $validTillField = new Field('validTill', Type::DateTime, isNullable: true);

        $blueprint->addField($nameField);
        $blueprint->addField($keyHashField);
        $blueprint->addField($validTillField);
        $blueprint->addIndex(new Index([$nameField]));
        $blueprint->addIndex(new Index([$keyHashField]));

        // Use the same named storage controller that owns this store, so the
        // schema is created in the correct storage rather than the default one.
        $storageController = $this->storageManager->controller($this->storageName);
        $actions = $storageController->actionBuilders()->createStore()
            ->build($store->storage(), $blueprint);

        $storageController->actionExecutor()->executeSet($actions);
    }

    public function getKeyHashes(string $name): array
    {
        $records = $this->storeController->fetch($this->get(), ['name' => $name]);
        $hashes = [];

        foreach ($records->fetchRecords() as $record) {
            $hashes[] = $record['keyHash'];
        }

        return $hashes;
    }
}
