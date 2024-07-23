<?php

declare(strict_types=1);

namespace Medas\ApiKeys;

use Medas\Core\Attributes\{EventListener, Service};

#[Service]
readonly class AuthHeaderVoteHandler
{
    public function __construct(
        private Validator $validator,
    )
    {
    }

    #[EventListener]
    public function handleRequest(AuthHeaderVote $authVote): void
    {
        if ($authVote->authorizationHeader === null) {
            // No Authorization header, we pass
            return;
        }

        if (!str_starts_with($authVote->authorizationHeader, 'Bearer ')) {
            $this->disAllow($authVote);

            return;
        }

        $token = substr($authVote->authorizationHeader, 7);

        if (!str_contains($token, ':')) {
            $this->disAllow($authVote);

            return;
        }

        [$name, $key] = explode(':', $token);

        if (!$this->validator->validate($name, $key)) {
            $this->disAllow($authVote);

            return;
        }

        $authVote->allowedAccess = true;
    }

    private function disAllow(AuthHeaderVote $authVote): void
    {
        $authVote->allowedAccess = false;
        $authVote->stopPropagation = true;
    }
}
