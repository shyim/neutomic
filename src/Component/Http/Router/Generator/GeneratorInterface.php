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

namespace Neu\Component\Http\Router\Generator;

use Neu\Component\Http\Exception\InvalidArgumentException;
use Neu\Component\Http\Exception\RouteNotFoundException;
use Neu\Component\Http\Message\UriInterface;

interface GeneratorInterface
{
    /**
     * Generate a path for a given route name.
     *
     * Parameters are optional and can be used to replace placeholders in the route pattern,
     * if extra parameters are provided, they will be appended as query string.
     *
     * @param non-empty-string $name
     * @param array<string, mixed> $parameters
     *
     * @throws RouteNotFoundException If the route name is not found.
     * @throws InvalidArgumentException If the parameters are invalid.
     */
    public function generate(string $name, array $parameters = []): UriInterface;
}
