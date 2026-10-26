<?php

namespace App\Exceptions;

use RuntimeException;

/** The message is a translation key; $replace fills its placeholders (e.g. :product). */
class CheckoutException extends RuntimeException
{
    public function __construct(string $key, public readonly array $replace = [])
    {
        parent::__construct($key);
    }
}
