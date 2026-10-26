<?php

namespace App\Exceptions;

use RuntimeException;

/** The message is a translation key (e.g. storefront.cart.insufficient_stock), resolved by the controller. */
class CartException extends RuntimeException
{
}
