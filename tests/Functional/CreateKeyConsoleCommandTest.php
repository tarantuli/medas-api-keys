<?php

declare(strict_types=1);

namespace Medas\ApiKeysTest\Functional;

use Medas\ConsolePrinter\CommandProcessor;
use PHPUnit\Framework\TestCase;

class CreateKeyConsoleCommandTest extends TestCase
{
    public function testCreate(): void
    {
        // Create a key
        ob_start();

        service(CommandProcessor::class)->process(['api-keys:create-key', 'my backend']);

        $output = ob_get_clean();

        self::assertStringContainsString('API key', $output);

        preg_match('/[a-f0-9]{64}/', $output, $key1);

        // Create another key under the same name
        ob_start();

        service(CommandProcessor::class)->process(['api-keys:create-key', 'my backend']);

        $output = ob_get_clean();

        preg_match('/[a-f0-9]{64}/', $output, $key2);

        self::assertStringContainsString('API key', $output);

        // Assert that the two keys differ
        self::assertNotEquals($key2, $key1);
    }
}
