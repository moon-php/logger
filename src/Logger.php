<?php

namespace Moon\Logger;

use Moon\Logger\Handler\AbstractHandler;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;

class Logger extends AbstractLogger
{
    /**
     * @var string $name Logger name
     */
    private $name;

    /**
     * @var AbstractHandler[] $handlers
     */
    private $handlers = [];

    /**
     * @var array $levels Contains all accepted levels
     */
    private $levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

    /**
     * Logger constructor.
     *
     * @param $name
     * @param array $handlers
     */
    public function __construct($name, array $handlers)
    {
        $this->name = $name;

        if (count($handlers) == 0) {
            throw new InvalidArgumentException("At least one handler must be passed");
        }

        foreach ($handlers as $key => $handler) {
            if (!$handler instanceof AbstractHandler) {
                throw new InvalidArgumentException(
                    "Handler must implement Moon\\Logger\\Handler\\HandlerInterface. Error with handler with key: $key"
                );
            }
            $this->handlers[] = $handler;
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        // throw an InvalidArgumentException if the level to log isn't in AbstractLogger
        if (!in_array($level, $this->levels)) {
            throw new InvalidArgumentException(
                "The level MUST be one of the constants contained by the Psr\\Log\\LogLevel class. Given $level"
            );
        }

        // throw an InvalidArgumentException if the message isn't an invalid format
        if (is_array($message) || (is_object($message) && !method_exists($message, '__toString'))) {
            throw new InvalidArgumentException("Message must be a string or an object implementing __toString() method");
        }

        // Pass the log to the handlers
        foreach ($this->handlers as $handler) {
            /** @var AbstractHandler $handler */
            $handler->add($this->name, $level, $message, $context);
        }
    }
}