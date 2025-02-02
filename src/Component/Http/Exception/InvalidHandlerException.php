<?php

declare(strict_types=1);

/*
 * This file is part of the Neutomic package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neu\Component\Http\Exception;

use Neu\Component\Http\Runtime\Handler\HandlerInterface;

final class InvalidHandlerException extends InvalidArgumentException
{
    public static function forHandler(mixed $handler): self
    {
        return new self(
            'Invalid handler provided. Expected an instance of ' . HandlerInterface::class . ', got ' . get_debug_type($handler) . '.',
        );
    }
}
