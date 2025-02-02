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

namespace Neu\Component\Http\Message;

use Amp\ByteStream\Payload;
use Amp\ByteStream\PendingReadError;
use Amp\ByteStream\ReadableIterableStream;
use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\StreamException;
use Amp\Sync\LocalSemaphore;
use Amp\Sync\Semaphore;
use Amp\TimeoutCancellation;
use Amp\TimeoutException as AmpTimeoutException;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Exception\TimeoutException;
use Neu\Component\Http\Exception\LogicException;
use Traversable;

final class Body implements BodyInterface
{
    private BodyMode $mode;
    private Payload $payload;
    private Semaphore $semaphore;

    /**
     * Constructs a new instance of {@see Body} with a specific {@see Payload}.
     *
     * @param Payload $payload The payload, encapsulating the actual data of the body.
     */
    public function __construct(Payload $payload)
    {
        $this->mode = BodyMode::None;
        $this->payload = $payload;
        $this->semaphore = new LocalSemaphore(1);

        // Set the mode to closed when the payload is closed.
        $payload->onClose(function () {
            $this->mode = BodyMode::Closed;
        });
    }

    /**
     * Factory method to create a {@see Body} from an iterable source.
     *
     * @param iterable<string> $iterable The iterable.
     *
     * @return self The new body.
     */
    public static function fromIterable(iterable $iterable): self
    {
        return new self(new Payload(new ReadableIterableStream($iterable)));
    }

    /**
     * Factory method to create a {@see Body} from a readable stream.
     *
     * @param ReadableStream $stream The stream.
     *
     * @return self The new body.
     */
    public static function fromReadableStream(ReadableStream $stream): self
    {
        return new self(new Payload($stream));
    }

    /**
     * Factory method to create a {@see Body} from a string.
     *
     * @param string $body The body.
     *
     * @return self The new body.
     */
    public static function fromString(string $body): self
    {
        return new self(new Payload($body));
    }

    /**
     * Factory method to create an empty {@see Body}.
     *
     * @return self The new body.
     */
    public static function empty(): self
    {
        return new self(new Payload(''));
    }

    /**
     * @inheritDoc
     */
    public function getMode(): BodyMode
    {
        return $this->mode;
    }

    /**
     * @inheritDoc
     */
    public function getChunk(null|float $timeout = null): null|string
    {
        if ($this->mode === BodyMode::Closed) {
            throw new LogicException('Cannot read from a closed body');
        }

        if ($this->mode === BodyMode::Buffered) {
            throw new LogicException('Cannot read from a buffered body as a chunk');
        }

        $this->mode = BodyMode::Streamed;

        $lock = $this->semaphore->acquire();
        try {
            if (null === $timeout) {
                return $this->payload->read();
            }

            return $this->payload->read(new TimeoutCancellation($timeout));
        } catch (AmpTimeoutException $e) {
            throw new TimeoutException('Reading from the body timed out', 0, $e);
        } catch (StreamException | PendingReadError $e) {
            throw new RuntimeException('An error occurred while reading from the body', 0, $e);
        } finally {
            $lock->release();
        }
    }

    /**
     * @inheritDoc
     */
    public function getContents(null|float $timeout = null): string
    {
        if ($this->mode === BodyMode::Buffered) {
            throw new LogicException('Cannot buffer a body more than once');
        }

        if ($this->mode === BodyMode::Closed) {
            throw new LogicException('Cannot read from a closed body');
        }

        if ($this->mode === BodyMode::Streamed) {
            throw new LogicException('Cannot read from a streamed body as a whole');
        }

        $this->mode = BodyMode::Buffered;

        $lock = $this->semaphore->acquire();
        try {
            if (null === $timeout) {
                return $this->payload->buffer();
            }

            return $this->payload->buffer(new TimeoutCancellation($timeout));
        } catch (AmpTimeoutException $e) {
            throw new TimeoutException('Reading from the body timed out', 0, $e);
        } catch (StreamException | PendingReadError $e) {
            throw new RuntimeException('An error occurred while reading from the body', 0, $e);
        } finally {
            $lock->release();
        }
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        if ($this->mode === BodyMode::Closed) {
            throw new LogicException('Cannot read from a closed body');
        }

        if ($this->mode === BodyMode::Buffered) {
            throw new LogicException('Cannot read from a buffered body as a chunk');
        }

        $this->mode = BodyMode::Streamed;

        $lock = $this->semaphore->acquire();
        try {
            while (null !== $chunk = $this->payload->read()) {
                yield $chunk;
            }
        } catch (StreamException | PendingReadError $e) {
            throw new RuntimeException('An error occurred while reading from the body', 0, $e);
        } finally {
            $lock->release();
        }
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->payload->close();
    }

    /**
     * Destructs the body, closing it if it hasn't been closed yet.
     */
    public function __destruct()
    {
        $this->close();
    }
}
