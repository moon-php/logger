<?php

namespace Moon\Logger\Unit\Handler;

use Moon\Logger\Formatter\FormatterInterface;
use Moon\Logger\Handler\RotatingFileHandler;
use org\bovigo\vfs\vfsStream;
use ReflectionObject;

class RotatingFileHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $formatter FormatterInterface
     */
    private $formatter;

    /**
     * Mock the filesystem on setup
     */
    public static function setUpBeforeClass()
    {
        vfsStream::setup('home/');
    }

    /**
     * Test if the RotatingFileHandler write on file
     *
     * @param $expectedContent
     * @param $pathToFile
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
        $this->assertEquals($expectedContent, file_get_contents($getRotatedFilename->invoke($fileHandler)));
    }

    /**
     * Test that the filename is different
     */
    public function testGetRotatedFilename()
    {
        $formatter = $this->getMockBuilder(FormatterInterface::class)->getMock();
        $fileHandler = new RotatingFileHandler($formatter, vfsStream::url('home/file'), new \DateInterval('PT1S'));

        $reflection = new ReflectionObject($fileHandler);
        $getRotatedFilename = $reflection->getMethod('getRotatedFilename');
        $getRotatedFilename->setAccessible(true);

        $filename = $getRotatedFilename->invoke($fileHandler);
        sleep(2);
        $anotherFilename = $getRotatedFilename->invoke($fileHandler);
        $this->assertTrue($filename != $anotherFilename);
    }


    /**
     * Return strings for testFileHandler
     *
     * @return array
     */
    public function formatterInfoDataProvider()
    {
        return [
            ["This is a log string" . PHP_EOL, vfsStream::url('home/file')],
            ["This is a log string" . PHP_EOL . "This is a log string" . PHP_EOL, vfsStream::url('home/file')]
        ];
    }

    /**
     * @return array
     */
    public function sampleDataProvider()
    {
        return [
            ['vfs://home/asd', vfsStream::url('home/file')],
            ['vfs://home/das', vfsStream::url('home/file')]
        ];
    }
}