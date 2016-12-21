<?php

namespace Moon\Logger\Handler;

use Moon\Logger\Formatter\FormatterInterface;

abstract class AbstractHandler
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * AbstractHandler constructor.
     * @param FormatterInterface $formatter
     */
    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
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
    abstract public function add($name, $level, $message, array $context = []);
}