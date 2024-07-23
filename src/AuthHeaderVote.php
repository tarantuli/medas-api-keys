<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Psr\EventDispatcher\StoppableEventInterface;

class AuthHeaderVote implements StoppableEventInterface
{
    public bool|null $allowedAccess = null;
    public bool $stopPropagation = false;
    public string|null $name = null;

    public function __construct(
        public string|null $authorizationHeader,
    )
    {
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopPropagation;
    }
}
