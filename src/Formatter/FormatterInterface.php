<?php

declare(strict_types=1);

namespace Moon\Logger\Formatter;

interface FormatterInterface
{
    /**
     * Return the string to log, replacing the placeholders.
     */
    public function interpolate(string $name, string $level, string $message, array $context = []): string;
}
