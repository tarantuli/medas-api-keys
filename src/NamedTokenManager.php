<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Medas\Core\{Attributes\Service, Interfaces\BearerTokenValidator};

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

    public function userId(string $token): string|null
    {
        if (substr_count($token, ':') !== 1) {
            return null;
        }

        [$name, $key] = explode(':', $token);

        if (!$this->validator->validate($name, $key)) {
            return null;
        }

        return $name;
    }
}
