<?php

declare(strict_types=1);

namespace Medas\ApiKeys\ConsoleCommands;

use Medas\ApiKeys\{Exceptions\InvalidKeyName, KeyCreator, KeyStoreManager};
use Medas\Console\{
    Commands\Argument,
    Commands\BaseConsoleCommand,
    Commands\CommandInput,
    Commands\ConsoleCommandGroup,
    Formats\SafeColor,
    Printer,
    Text
};
use Medas\Core\Attributes\{Entrypoint, Service};

#[Service, Entrypoint]
readonly class CreateKey extends BaseConsoleCommand
{
    public function __construct(
        private ApiKeysConsoleCommandGroup $group,
        private KeyCreator                 $keyCreator,
        private KeyStoreManager            $keyStoreManager,
        private Printer                    $printer,
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

    public function arguments(): array
    {
        return [
            Argument::required('name'),
        ];
    }

    public function process(CommandInput $input): void
    {
        $name = $input->getArgument('name');

        if (!preg_match('/^\S+$/', $name)) {
            throw new InvalidKeyName($name);
        }

        $key = $this->keyCreator->create();

        $this->keyStoreManager->storeKey($name, $key);

        $this->printer->print(
            Text::create('API key: ', SafeColor::White),
            Text::create($key, SafeColor::LightGray)
        )->printEol();
    }
}
