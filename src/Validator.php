<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Medas\Core\Attributes\Service;

#[Service]
readonly class Validator
{
    public function __construct(
        private KeyStoreManager $keyStoreManager,
    )
    {
    }

    public function validate(string $name, string $key): bool
    {
        // NOTE: caching validation results improves performance by avoiding
        // repeated bcrypt calls within the same request, but be aware that
        // a persistent cache (Redis, Memcached, etc.) will keep a positive
        // result alive after a key is deleted until the entry expires.
        // Ensure your cache() implementation uses a short TTL or is
        // request-scoped when key revocation needs to take effect immediately.
        return cache([__CLASS__, $name, $key], fn() => $this->verify($name, $key));
    }

    private function verify(string $name, string $key): bool
    {
        $hashes = $this->keyStoreManager->getKeyHashes($name);

        foreach ($hashes as $hash) {
            if (password_verify($key, $hash)) {
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    // Pass the old hash so storeKey can replace it atomically
                    // rather than leaving a stale copy in the store.
                    $this->keyStoreManager->storeKey($name, $key, $hash);
                }

                return true;
            }
        }

        return false;
    }
}
