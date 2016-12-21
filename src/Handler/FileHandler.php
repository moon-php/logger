<?php

namespace Moon\Logger\Handler;

use Moon\Logger\Formatter\FormatterInterface;

class FileHandler extends AbstractHandler
{
    /**
     * @var string $filename
     */
    private $filename;

    /**
     * FileHandler constructor.
     *
     * @param FormatterInterface $formatter
     * @param $filename
     */
    public function __construct(FormatterInterface $formatter, $filename)
    {
        parent::__construct($formatter);
        $this->filename = $filename;
    }

    /**
     * Format the message and push it somewhere
     *
     * @param $name
     * @param $level
     * @param $message
     * @param array $context
     *
     * @return void
     */
    public function add($name, $level, $message, array $context = [])
    {
        // Format the message
        $data = $this->formatter->interpolate($name, $level, $message, $context);

        // Push it to file
        file_put_contents($this->filename, $data . PHP_EOL, FILE_APPEND);
    }
}