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
        $hashes = $this->keyStoreManager->getKeyHashes($name);

        foreach ($hashes as $hash) {
            if (password_verify($key, $hash)) {
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $this->keyStoreManager->storeKey($name, $key);
                }

                return true;
            }
        }

        return false;
    }
}
