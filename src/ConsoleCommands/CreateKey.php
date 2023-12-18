<?php

declare(strict_types=1);

namespace Medas\ApiKeys\ConsoleCommands;

use Medas\ApiKeys\{KeyCreator, KeyStoreManager};
use Medas\Console\{Commands\BaseConsoleCommand, Commands\ConsoleCommandGroup, Formats\Color, Text};
use Medas\ConsolePrinter\ConsolePrinter;
use Medas\Core\Attributes\Service;

#[Service]
readonly class CreateKey extends BaseConsoleCommand
{
    public function __construct(
        private ConsolePrinter  $printer,
        private KeyCreator      $keyCreator,
        private KeyStoreManager $keyStoreManager,
        private RootGroup       $group,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'create-key';
    }

    public function description(): string
    {
        return 'Create a new API key';
    }

    public function process(array $arguments): void
    {
        $name = $arguments[1];
        $key = $this->keyCreator->create();

        $this->keyStoreManager->storeKey($name, $key);

        $this->printer->print(
            Text::create('API key: ', Color::White),
            Text::create($key, Color::LightGray)
        )->printEol();
    }
}
