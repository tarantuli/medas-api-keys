<?php

declare(strict_types=1);

namespace Medas\ApiKeys\Exceptions;

use Medas\Core\Exceptions\BaseException;

class InvalidKeyName extends BaseException
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function pattern(): string
    {
        return '%s is not a valid name containing only non-whitespace characters';
    }
}
