<?php

declare(strict_types=1);

namespace Moon\Logger\Handler;

interface HandlerInterface
{
    /**
     * Add log
     *
     * @param string $name
     * @param string $level
     * @param mixed $message
     * @param array $context
     */
    public function add(string $name, string $level, $message, array $context = []): void;
}