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

namespace Neu\Component\DependencyInjection\Exception;

use RuntimeException as RootRuntimeException;

class RuntimeException extends RootRuntimeException implements ExceptionInterface
{
    /**
     * Create a new instance for an empty service id.
     */
    public static function forEmptyServiceId(): self
    {
        return new self('Service id cannot be empty.');
    }
}
