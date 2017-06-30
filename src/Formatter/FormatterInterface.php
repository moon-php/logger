<?php

declare(strict_types=1);

namespace Moon\Logger\Formatter;


interface FormatterInterface
{
    /**
     * Return the string to log, replacing the placeholders.
     *
     * @param string $name
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public function interpolate($name, $level, $message, array $context = []): string;
}