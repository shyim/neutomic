<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection;

use Neu\Component\DependencyInjection\Exception\ExceptionInterface;

/**
 * A container hook which is invoked after the container is built.
 */
interface HookInterface
{
    /**
     * Invokes the hook.
     *
     * @param ContainerInterface $container The built container.
     *
     * @throws ExceptionInterface If an error occurs.
     */
    public function __invoke(ContainerInterface $container): void;
}
