<?php

declare(strict_types=1);

namespace Moon\Logger\Handler;

use DateTimeImmutable;
use Moon\Logger\Formatter\FormatterInterface;

class RotatingFileHandler implements HandlerInterface
{
    /**
     * @var string
     */
    private $filename;
    /**
     * @var \DateInterval
     */
    private $rotateEvery;
    /**
     * @var DateTimeImmutable
     */
    private $rotationStartedAt;
    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var string
     */
    private $defaultCurrentTime = 'now';

    public function __construct(FormatterInterface $formatter, string $filename, \DateInterval $rotateEvery = null)
    {
        $this->filename = $filename;
        $this->formatter = $formatter;
        $this->rotateEvery = $rotateEvery ?: new \DateInterval('P1D');
        $this->rotationStartedAt = new DateTimeImmutable('now');
    }

    public function add(string $name, string $level, $message, array $context = []): void
    {
        // Format the message
        $data = $this->formatter->interpolate($name, $level, $message, $context);

        // Push it to file
        \file_put_contents($this->getRotatedFilename(), $data.PHP_EOL, FILE_APPEND);
    }

    /**
     * Return the file name appending the $this->rotationStartedAt.
     */
    private function getRotatedFilename(): string
    {
        // Check if a new file must be used
        $now = $this->getCurrentDateTime();
        if ($now > $this->rotationStartedAt->add($this->rotateEvery)) {
            $this->rotationStartedAt = $now;
        }

        // Get file name appending data by on $this->rotationEvery
        return \preg_replace('/(^.*\/[^.\/]+)(\.[^.\/]+)?$/', "$1_{$this->rotationStartedAt->format('Y-M-d H:m:s')}$2",
            $this->filename);
    }

    /**
     * Return a current DateTime. Extracted for be easily tested.
     */
    private function getCurrentDateTime(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->defaultCurrentTime);
    }
}
