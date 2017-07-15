<?php

declare(strict_types=1);

namespace Moon\Logger\Handler;

use Moon\Logger\Formatter\FormatterInterface;

class FileHandler implements HandlerInterface
{
    /**
     * @var string $filename
     */
    private $filename;
    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * FileHandler constructor.
     *
     * @param FormatterInterface $formatter
     * @param string $filename
     */
    public function __construct(FormatterInterface $formatter, string $filename)
    {
        $this->formatter = $formatter;
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $name, string $level, $message, array $context = []): void
    {
        // Format the message
        $data = $this->formatter->interpolate($name, $level, $message, $context);

        // Push it to file
        file_put_contents($this->filename, $data . PHP_EOL, FILE_APPEND);
    }
}