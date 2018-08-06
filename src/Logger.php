<?php

declare(strict_types=1);

namespace Moon\Logger;

use Moon\Logger\Handler\HandlerInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger
{
    /**
     * @var string Logger name
     */
    private $name;

    /**
     * @var HandlerInterface[]
     */
    private $handlers = [];

    /**
     * @var array Contains all accepted levels
     */
    private $levels = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG,
    ];

    public function __construct(string $name, array $handlers)
    {
        $this->name = $name;

        if (0 === \count($handlers)) {
            throw new InvalidArgumentException('At least one handler must be passed');
        }

        foreach ($handlers as $key => $handler) {
            if (!$handler instanceof HandlerInterface) {
                throw new InvalidArgumentException(
                    \sprintf('Handler must implement %s. Error with handler with key: %s', HandlerInterface::class, $key)
                );
            }
            $this->handlers[] = $handler;
        }
    }

    public function log($level, $message, array $context = []): void
    {
        // throw an InvalidArgumentException if the level to log isn't in AbstractLogger
        if (!\in_array($level, $this->levels, true)) {
            throw new InvalidArgumentException(\sprintf(
                'The level MUST be one of the constants contained by the %s class. Given %s', LogLevel::class, $level
            ));
        }

        // throw an InvalidArgumentException if the message isn't an invalid format
        if (\is_array($message) || (\is_object($message) && !\method_exists($message, '__toString'))) {
            throw new InvalidArgumentException('Message must be a string or an object implementing __toString() method');
        }

        // Pass the log to the handlers
        foreach ($this->handlers as $handler) {
            /* @var HandlerInterface $handler */
            $handler->add($this->name, $level, $message, $context);
        }
    }
}
