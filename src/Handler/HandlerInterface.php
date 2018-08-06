<?php

declare(strict_types=1);

namespace Moon\Logger\Handler;

interface HandlerInterface
{
    public function add(string $name, string $level, $message, array $context = []): void;
}
