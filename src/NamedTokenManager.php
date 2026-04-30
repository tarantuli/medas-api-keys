<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    Events\DebugInformation,
    Interfaces\AuthenticationData,
    Interfaces\AuthenticationTokenController
};
use Medas\Json\JsonEncoder;

#[Service]
readonly class NamedTokenManager implements AuthenticationTokenController
{
    public function __construct(
        private JsonEncoder     $jsonEncoder,
        private KeyCreator      $keyCreator,
        private KeyStoreManager $keyStoreManager,
        private Validator       $validator,

        #[ConfigValue(ConfigOptions\ValidateNamedTokens::class)]
        private bool            $validateNamedTokens,
    )
    {
    }

    public function create(AuthenticationData $data): string
    {
        $key = $this->keyCreator->create();
        $name = $this->jsonEncoder->encode($data);

        $this->keyStoreManager->storeKey($name, $key);

        return "$name:$key";
    }

    public function invalidate(string $token): void
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

    public function data(string $token): AuthenticationData|null
    {
        if (substr_count($token, ':') !== 1) {
            dispatch(new DebugInformation('[named-token-manager] token does not contain exactly one colon'));

            return null;
        }

        [$name, $key] = explode(':', $token);

        if ($this->validateNamedTokens) {
            if (!$this->validator->validate($name, $key)) {
                dispatch(new DebugInformation('[named-token-manager] key "%s" is invalid for name "%s"', $key, $name));

                return null;
            }
        }
        else {
            // SECURITY: token validation is disabled via config; any token with
            // a valid format is accepted without a store lookup. Only disable
            // this in controlled environments (e.g., local development).
            dispatch(new DebugInformation(
                '[named-token-manager] token validation is disabled; accepting token for name "%s" without verification',
                $name
            ));
        }

        return $this->jsonEncoder->decode($name);
    }

    public function userId(string $token): string|null
    {
        return $this->data($token)?->getUserId();
    }
}
