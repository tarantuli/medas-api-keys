<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Medas\Core\{Attributes\Service, Events\DebugInformation, Interfaces\BearerTokenValidator};

#[Service]
readonly class NamedTokenManager implements BearerTokenValidator
{
    public function __construct(
        private KeyCreator      $keyCreator,
        private KeyStoreManager $keyStoreManager,
        private Validator       $validator,
    )
    {
    }

    public function create(string $name): string
    {
        $key = $this->keyCreator->create();

        $this->keyStoreManager->storeKey($name, $key);

        return "$name:$key";
    }

    public function delete(string $token): void
    {
        if (!$this->userId($token)) {
            return;
        }

        [$name, $key] = explode(':', $token);
        $hashes = $this->keyStoreManager->getKeyHashes($name);

        foreach ($hashes as $hash) {
            if (password_verify($key, $hash)) {
                $this->keyStoreManager->deleteHash($hash);
            }
        }
    }

    public function userId(string $token): string|null
    {
        if (substr_count($token, ':') !== 1) {
            dispatch(new DebugInformation('[named-token-manager] token does not contain exactly one colon'));

            return null;
        }

        [$name, $key] = explode(':', $token);

        if (!$this->validator->validate($name, $key)) {
            dispatch(new DebugInformation('[named-token-manager] key "%s" is invalid for name "%s"', $key, $name));

            return null;
        }

        return $name;
    }
}
