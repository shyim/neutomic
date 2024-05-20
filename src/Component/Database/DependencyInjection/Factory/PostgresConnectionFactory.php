<?php

declare(strict_types=1);

namespace Neu\Component\Database\DependencyInjection\Factory;

use Amp\Postgres;
use Amp\Postgres\PostgresConfig;
use Amp\Postgres\PostgresConnection;
use Amp\Sql\SqlException;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\InvalidArgumentException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see PostgresConnection} instance.
 *
 * @implements FactoryInterface<PostgresConnection>
 */
final readonly class PostgresConnectionFactory implements FactoryInterface
{
    /**
     * The hostname of the Postgres server.
     */
    private string $host;

    /**
     * The port of the Postgres server.
     */
    private ?int $port;

    /**
     * The username for the Postgres connection.
     */
    private ?string $user;

    /**
     * The password for the Postgres connection.
     */
    private ?string $password;

    /**
     * The database name for the Postgres connection.
     */
    private ?string $database;

    /**
     * The application name for the Postgres connection.
     */
    private ?string $applicationName;

    /**
     * The SSL mode for the Postgres connection.
     *
     * @var value-of<PostgresConfig::SSL_MODES>|null
     */
    private ?string $sslMode;

    /**
     * Create a new Postgres connection factory.
     *
     * @param string $host The hostname of the Postgres server.
     * @param int|null $port The port of the Postgres server.
     * @param string|null $user The username for the Postgres connection.
     * @param string|null $password The password for the Postgres connection.
     * @param string|null $database The database name for the Postgres connection.
     * @param string|null $applicationName The application name for the Postgres connection.
     * @param value-of<PostgresConfig::SSL_MODES>|null $sslMode The SSL mode for the Postgres connection.
     */
    public function __construct(
        string $host,
        ?int $port = null,
        ?string $user = null,
        ?string $password = null,
        ?string $database = null,
        ?string $applicationName = null,
        ?string $sslMode = null,
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->applicationName = $applicationName;
        $this->sslMode = $sslMode;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): PostgresConnection
    {
        $config = new PostgresConfig(
            host: $this->host,
            port: $this->port ?? PostgresConfig::DEFAULT_PORT,
            user: $this->user,
            password: $this->password,
            database: $this->database,
            applicationName: $this->applicationName,
            sslMode: $this->sslMode,
        );

        try {
            return Postgres\connect($config);
        } catch (SqlException $e) {
            throw new InvalidArgumentException('Failed to connect to the database: ' . $e->getMessage(), 0, $e);
        }
    }
}
