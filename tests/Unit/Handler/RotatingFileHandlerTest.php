<?php

declare(strict_types=1);

namespace Moon\Logger\Unit\Handler;

use Moon\Logger\Formatter\FormatterInterface;
use Moon\Logger\Handler\RotatingFileHandler;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

class RotatingFileHandlerTest extends TestCase
{
    /**
     * Mock the filesystem on setup.
     */
    public static function setUpBeforeClass()
    {
        vfsStream::setup('home/');
    }

    /**
     * Test if the RotatingFileHandler write on file.
     *
     * @dataProvider formatterInfoDataProvider
     */
    public function testRotatingFileHandler($expectedContent, $pathToFile)
    {
        $formatter = $this->getMockBuilder(FormatterInterface::class)->getMock();
        $formatter->expects($this->once())->method('interpolate')->will($this->returnValue('This is a log string'));

        $fileHandler = new RotatingFileHandler($formatter, $pathToFile);

        $reflection = new ReflectionObject($fileHandler);
        $getRotatedFilename = $reflection->getMethod('getRotatedFilename');
        $getRotatedFilename->setAccessible(true);

        $fileHandler->add('name', 'level', 'message');
        $this->assertEquals($expectedContent, \file_get_contents($getRotatedFilename->invoke($fileHandler)));
    }

    /**
     * Test that the filename is different.
     */
    public function testGetRotatedFilename()
    {
        $formatter = $this->getMockBuilder(FormatterInterface::class)->getMock();
        $fileHandler = new RotatingFileHandler($formatter, vfsStream::url('home/file'), new \DateInterval('PT1S'));

        $reflection = new ReflectionObject($fileHandler);
        $getRotatedFilename = $reflection->getMethod('getRotatedFilename');
        $getRotatedFilename->setAccessible(true);
        $defaultCurrentTime = $reflection->getProperty('defaultCurrentTime');
        $defaultCurrentTime->setAccessible(true);

        $filename = $getRotatedFilename->invoke($fileHandler);
        $defaultCurrentTime->setValue($fileHandler, '+1 second');
        $anotherFilename = $getRotatedFilename->invoke($fileHandler);
        $this->assertNotEquals($filename, $anotherFilename);
    }

    /**
     * Return strings for testFileHandler.
     */
    public function formatterInfoDataProvider(): array
    {
        return [
            ['This is a log string'.PHP_EOL, vfsStream::url('home/file')],
            ['This is a log string'.PHP_EOL.'This is a log string'.PHP_EOL, vfsStream::url('home/file')],
        ];
    }

    public function sampleDataProvider(): array
    {
        return [
            ['vfs://home/asd', vfsStream::url('home/file')],
            ['vfs://home/das', vfsStream::url('home/file')],
        ];
    }
}
