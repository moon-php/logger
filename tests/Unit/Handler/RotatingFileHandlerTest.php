<?php

namespace Moon\Logger\Unit\Handler;

use Moon\Logger\Formatter\FormatterInterface;
use Moon\Logger\Handler\RotatingFileHandler;
use org\bovigo\vfs\vfsStream;
use ReflectionMethod;
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
     * Mock formatter
     */
    public function setUp()
    {
        $formatter = $this->getMockBuilder(FormatterInterface::class)->getMock();
        $formatter->expects($this->once())
            ->method('interpolate')
            ->will($this->returnValue('This is a log string'));
        $this->formatter = $formatter;
    }

    /**
     * Test if the RotatingFileHandler write on file
     *
     * @param $expectedContent
     * @param $pathToFile
     *
     * @dataProvider formatterInfoDataProvider
     */
    public function testFileHandler($expectedContent, $pathToFile)
    {
        $fileHandler = new RotatingFileHandler($this->formatter, $pathToFile);

        $reflection = new ReflectionObject($fileHandler);
        $getRotatedFilename = $reflection->getMethod('getRotatedFilename');
        $getRotatedFilename->setAccessible(true);

        $fileHandler->add('name', 'level', 'message');
        $this->assertEquals($expectedContent, file_get_contents($getRotatedFilename->invoke($fileHandler)));
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
}