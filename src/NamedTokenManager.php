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
use Medas\ObjectToArraySerializer\ObjectToArraySerializer;

#[Service]
readonly class NamedTokenManager implements AuthenticationTokenController
{
    public function __construct(
        private JsonEncoder             $jsonEncoder,
        private KeyCreator              $keyCreator,
        private KeyStoreManager         $keyStoreManager,
        private ObjectToArraySerializer $objectToArraySerializer,
        private Validator               $validator,

        #[ConfigValue(ConfigOptions\ValidateNamedTokens::class)]
        private bool                    $validateNamedTokens,
    )
    {
    }

    public function create(AuthenticationData $data): string
    {
        $key = $this->keyCreator->create();
        $dataString = $this->jsonEncoder->encode($this->objectToArraySerializer->serialize($data));

        $this->keyStoreManager->storeKey($dataString, $key);

        return base64_encode($dataString) . '_' . base64_encode($data::class) . ':' . $key;
    }

    public function invalidate(string $token): void
    {
        if (!$this->userId($token)) {
            return;
        }

        [$name, $key] = explode(':', $token);

        [
            $dataString,
        ] = explode('_', $name);

        $dataString = base64_decode($dataString);
        $hashes = $this->keyStoreManager->getKeyHashes($dataString);

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
        [$dataString, $dataType] = explode('_', $name);
        $dataString = base64_decode($dataString);
        $dataType = base64_decode($dataType);

        if ($this->validateNamedTokens) {
            if (!$this->validator->validate($dataString, $key)) {
                dispatch(new DebugInformation(
                    '[named-token-manager] key "%s" is invalid for name "%s"',
                    $key,
                    $dataString
                ));

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

        /** @var AuthenticationData */
        return $this->objectToArraySerializer->unserialize(
            $this->jsonEncoder->decode($dataString),
            class: $dataType
        );
    }

    public function userId(string $token): mixed
    {
        return $this->data($token)?->getUserId();
    }
}
