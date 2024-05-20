<?php

declare(strict_types=1);

namespace Neu\Component\Cache\DependencyInjection\Factory;

use Neu\Component\Cache\Driver\DriverInterface;
use Neu\Component\Cache\Store;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * A factory for creating a {@see Store} instance.
 *
 * @implements FactoryInterface<Store>
 */
final readonly class StoreFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $driver;

    /**
     * @param null|non-empty-string $driver The service identifier of the driver to use, defaults to {@see DriverInterface}.
     */
    public function __construct(?string $driver = null)
    {
        $this->driver = $driver ?? DriverInterface::class;
    }

    public function __invoke(ContainerInterface $container): Store
    {
        return new Store(
            $container->getTyped($this->driver, DriverInterface::class)
        );
    }
}
