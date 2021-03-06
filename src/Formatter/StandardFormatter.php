<?php

declare(strict_types=1);

namespace Moon\Logger\Formatter;

use Exception;

class StandardFormatter implements FormatterInterface
{
    /**
     * The string to return is composed by some placeholders
     * {date} timestamp of the log
     * {name} logger name
     * {level} log level
     * {message} message to log (can contains placeholder too)
     * {context} un-replaced variables
     * {exception} exception trace.
     *
     * @var string
     */
    private $format = '[{date}] {name}.{level}: {message} [{context}] [{exception}]';

    /**
     * @var string dateFormat
     */
    private $dateFormat;

    public function __construct($dateFormat = 'Y-m-d H:i:s')
    {
        $this->dateFormat = $dateFormat;
    }

    public function interpolate(string $name, string $level, string $message, array $context = []): string
    {
        foreach ($context as $key => $value) {
            // If in the context array there's a key with an \Exception as value
            if ('exception' === $key && $value instanceof Exception) {
                // Get the full error trace as string
                $exceptionTrace = '';
                while ($previous ?? true) {
                    // Create the trace string
                    $exceptionTrace .= '( '.\get_class($value).": (code:  {$value->getCode()} ):  {$value->getMessage()}  at  {$value->getFile()} : {$value->getLine()}),";
                    // If there's no previous exception, stop the loop
                    if (!$value = $value->getPrevious()) {
                        $previous = false;
                    }
                }
                // Trim the last comma and remove all the break lines
                $exceptionTrace = \str_replace(PHP_EOL, ' ', \rtrim($exceptionTrace, ','));
                // Remove from the context and continue
                unset($context[$key]);
                continue;
            }

            // If the value isn't an array and is an object with a toString method
            if ((!\is_array($value) || (\is_object($value) && \method_exists($value, '__toString'))) && false !== \mb_strpos($message, '{'.$key.'}')) {
                // If in the message there's a placeholder (represented as {key}) replace it with the value,
                // remove from the context and continue
                $message = \str_replace('{'.$key.'}', "$value", $message);
                unset($context[$key]);
            }
        }

        // Handle the string to replace (empty if is not set)
        $context = \count($context) > 0 ? \json_encode($context) : '';
        $exceptionTrace = $exceptionTrace ?? '';

        // Replace and return the log line
        $data = \str_replace(
            ['{date}', '{name}', '{level}', '{context}', '{exception}', '{message}'],
            [$this->getTimestampFromImmutable(), $name, $level, $context, $exceptionTrace, $message],
            $this->format
        );

        return $data;
    }

    /**
     * Return a timestamp for logger
     * (Is a specific method for a better testability).
     */
    protected function getTimestampFromImmutable(): string
    {
        $date = new \DateTimeImmutable('now', new \DateTimeZone('utc'));

        return $date->format($this->dateFormat);
    }
}
