<?php

namespace Moon\Logger\Handler;

use Moon\Logger\Formatter\FormatterInterface;

class RotatingFileHandler extends AbstractHandler
{
    /**
     * @var string $filename
     */
    private $filename;
    /**
     * @var \DateInterval
     */
    private $rotateEvery;
    /**
     * @var \DateTimeImmutable
     */
    private $rotationStartedAt;

    /**
     * RotatingFileHandler constructor.
     *
     * @param FormatterInterface $formatter
     * @param $filename
     * @param \DateInterval $rotateEvery
     */
    public function __construct(FormatterInterface $formatter, $filename, \DateInterval $rotateEvery = null)
    {
        parent::__construct($formatter);
        $this->filename = $filename;
        $this->rotateEvery = $rotateEvery ?: new \DateInterval('P1D');
        $this->rotationStartedAt = new \DateTimeImmutable('now');
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
        file_put_contents($this->getRotatedFilename(), $data . PHP_EOL, FILE_APPEND);
    }

    /**
     * Return the file name appending the $this->rotationStartedAt
     *
     * @return string
     */
    private function getRotatedFilename()
    {
        // Check if a new file must be used
        $now = $this->getCurrentDateTime();
        if ($now > $this->rotationStartedAt->add($this->rotateEvery)) {
            $this->rotationStartedAt = $now;
        }

        // Get file name appending data by on $this->rotationEvery
        return preg_replace('/(^.*\/[^.\/]+)(\.[^.\/]+)?$/', "$1\\{$this->rotationStartedAt->format('Y-M-d H:m:s')}$2", $this->filename);
    }

    /**
     * Return a current DateTime. Extracted for be easily tested.
     *
     * @return \DateTimeImmutable
     */
    private function getCurrentDateTime()
    {
        return new \DateTimeImmutable('now');
    }
}