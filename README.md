# medas-api-keys

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

Provides API key generation, storage, and validation. Keys are random lowercase hexadecimal strings hashed with `password_hash()` before being written to any storage backend. The package also ships `NamedTokenManager`, which implements `AuthenticationTokenController` and bundles serialised `AuthenticationData` into a self-describing bearer token — useful for stateless API authentication where the token itself carries user identity.

Key features:

- Cryptographically random key generation via `random_bytes()` / `bin2hex()`
- Bcrypt hashing at rest with automatic rehash-on-verify
- Optional multi-key support (multiple active keys per name)
- Named token format: `base64(data)_base64(class):key`
- Console command to create keys from the CLI
- Storage-backend-agnostic via `medas-storage-manager`

## Configuration options

| Option                  | Default                  | Description                                                               |
|-------------------------|--------------------------|---------------------------------------------------------------------------|
| `allow-multiple-keys`   | `true`                   | Allow more than one active key per name                                   |
| `default-key-length`    | `64`                     | Character length of generated keys                                        |
| `storage-name`          | `null` (default storage) | Storage backend to use                                                    |
| `store-name`            | `api-keys`               | Name of the store/table                                                   |
| `validate-named-tokens` | `true`                   | Verify key against the store on every request (disable only in local dev) |

## Usage

### Package developer context

Register the package and inject `KeyCreator` or `NamedTokenManager` wherever you need key functionality:

```php
use Medas\ApiKeys\ApiKeysPackage;
use Medas\ApiKeys\KeyCreator;
use Medas\ApiKeys\NamedTokenManager;
use Medas\Core\Attributes\Service;

// Register the package with the framework bootstrapper
ApiKeysPackage::instance();

// Inject KeyCreator to generate a standalone random key
#[Service]
readonly class MyService
{
    public function __construct(
        private KeyCreator $keyCreator,
    ) {}

    public function issueKey(): string
    {
        // Returns a 64-character hex string by default
        return $this->keyCreator->create();
    }

    public function issueShortKey(): string
    {
        // Override the length for a specific call
        return $this->keyCreator->create(length: 32);
    }
}

// Bind NamedTokenManager as the AuthenticationTokenController implementation
// in your DI configuration so the framework resolves it automatically.
```

To store and validate a key manually:

```php
use Medas\ApiKeys\KeyCreator;
use Medas\ApiKeys\KeyStoreManager;
use Medas\ApiKeys\Validator;
use Medas\Core\Attributes\Service;

#[Service]
readonly class KeyLifecycleExample
{
    public function __construct(
        private KeyCreator      $keyCreator,
        private KeyStoreManager $keyStoreManager,
        private Validator       $validator,
    ) {}

    public function provision(string $clientName): string
    {
        $key = $this->keyCreator->create();
        // Stores a bcrypt hash; the plain key is never persisted
        $this->keyStoreManager->storeKey($clientName, $key);

        return $key;
    }

    public function verify(string $clientName, string $key): bool
    {
        return $this->validator->validate($clientName, $key);
    }
}
```

### Backend user context

**Creating a key from the console:**

```bash
php bin/console api-keys:create-key my-client-name
# API key: 3f8a2c...
```

The plain key is printed once and never stored. Copy it to the client's configuration immediately.

**Validating an incoming API key in a request handler:**

```php
use Medas\ApiKeys\Validator;
use Medas\Core\Attributes\Service;

#[Service]
readonly class ApiKeyAuthenticator
{
    public function __construct(
        private Validator $validator,
    ) {}

    public function authenticate(string $clientName, string $providedKey): bool
    {
        // Returns true if the key matches a stored hash for the given name.
        // Rehashes automatically when the bcrypt cost factor has increased.
        return $this->validator->validate($clientName, $providedKey);
    }
}
```

**Using named tokens for bearer-token authentication:**

```php
use Medas\ApiKeys\NamedTokenManager;
use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\AuthenticationData;

#[Service]
readonly class TokenAuthController
{
    public function __construct(
        private NamedTokenManager $tokenManager,
    ) {}

    public function issue(AuthenticationData $data): string
    {
        // Returns a bearer token embedding the serialized AuthenticationData
        return $this->tokenManager->create($data);
    }

    public function resolve(string $bearerToken): AuthenticationData|null
    {
        // Returns the AuthenticationData, or null if the token is invalid
        return $this->tokenManager->data($bearerToken);
    }

    public function revoke(string $bearerToken): void
    {
        $this->tokenManager->invalidate($bearerToken);
    }
}
```
