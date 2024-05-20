<?php

declare(strict_types=1);

namespace Neu\Component\Http\Recovery\DependencyInjection;

use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\Http\Recovery\DependencyInjection\Factory\RecoveryFactory;
use Neu\Component\Http\Recovery\Recovery;
use Neu\Component\Http\Recovery\RecoveryInterface;
use Psl\Type;
use Psr\Log\LogLevel;
use Throwable;

/**
 * @psalm-import-type ThrowablesConfiguration from Recovery as RecoveryThrowablesConfiguration
 *
 * @psalm-type Configuration = array{
 *  logger?: non-empty-string,
 *  throwables?: RecoveryThrowablesConfiguration,
 * }
 */
final readonly class RecoveryExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        /** @var string|null $defaultLogger */
        $defaultLogger = $container
            ->getConfiguration()
            ->getContainer('http')
            ->getOfTypeOrDefault('logger', Type\string(), null)
        ;

        /** @var Configuration $configuration */
        $configuration = $container
            ->getConfiguration()
            ->getContainer('http')
            ->getOfTypeOrDefault('recovery', $this->getConfigurationType(), [])
        ;

        $container->addDefinition(Definition::ofType(Recovery::class, new RecoveryFactory(
            $configuration['logger'] ??  $defaultLogger ?? null,
            $configuration['throwables'] ?? [],
        )));

        $container->getDefinition(Recovery::class)->addAlias(RecoveryInterface::class);
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'logger' => Type\optional(Type\non_empty_string()),
            'throwables' => Type\optional(Type\dict(
                Type\class_string(Throwable::class),
                Type\shape([
                    'log_level' => Type\optional(Type\union(
                        Type\literal_scalar(LogLevel::DEBUG),
                        Type\literal_scalar(LogLevel::INFO),
                        Type\literal_scalar(LogLevel::NOTICE),
                        Type\literal_scalar(LogLevel::WARNING),
                        Type\literal_scalar(LogLevel::ERROR),
                        Type\literal_scalar(LogLevel::CRITICAL),
                        Type\literal_scalar(LogLevel::ALERT),
                        Type\literal_scalar(LogLevel::EMERGENCY),
                    )),
                    'status' => Type\optional(Type\int()),
                    'headers' => Type\optional(Type\dict(
                        Type\non_empty_string(),
                        Type\vec(Type\non_empty_string()),
                    )),
                ])
            )),
        ]);
    }
}
