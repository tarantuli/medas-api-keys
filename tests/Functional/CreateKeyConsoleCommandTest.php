<?php

declare(strict_types=1);

namespace Medas\ApiKeysTest\Functional;

use Medas\ApiKeys\Validator;
use Medas\ConsolePrinter\CommandProcessor;
use PHPUnit\Framework\TestCase;

class CreateKeyConsoleCommandTest extends TestCase
{
    private const KEY_NAME = 'my-backend';

    public function testCreate(): string
    {
        // Create a key
        ob_start();

        service(CommandProcessor::class)->process(['api-keys:create-key', self::KEY_NAME]);

        $output = ob_get_clean();

        self::assertStringContainsString('API key', $output);

        self::assertSame(1, preg_match('/([a-f0-9]{64})/', $output, $key1), 'First key not found in output');

        // Create another key under the same name
        ob_start();

        service(CommandProcessor::class)->process(['api-keys:create-key', self::KEY_NAME]);

        $output = ob_get_clean();

        self::assertStringContainsString('API key', $output);
        self::assertSame(1, preg_match('/([a-f0-9]{64})/', $output, $key2), 'Second key not found in output');

        // Assert that the two keys differ
        self::assertNotEquals($key2[1], $key1[1]);

        return $key2[1];
    }

    /** @depends testCreate */
    public function testCheck(string $key): void
    {
        $isValid = service(Validator::class)->validate(self::KEY_NAME, $key);

        self::assertTrue($isValid);
    }
}
