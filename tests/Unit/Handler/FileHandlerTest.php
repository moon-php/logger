<?php

declare(strict_types=1);

namespace Moon\Logger\Unit\Handler;

use Moon\Logger\Formatter\FormatterInterface;
use Moon\Logger\Handler\FileHandler;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class FileHandlerTest extends TestCase
{
    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * Mock the filesystem on setup.
     */
    public static function setUpBeforeClass()
    {
        vfsStream::setup('home/');
    }

    /**
     * Mock formatter.
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
     * Test if the FileHandler write on file.
     *
     * @dataProvider formatterInfoDataProvider
     */
    public function testFileHandler($expectedContent, $pathToFile)
    {
        $fileHandler = new FileHandler($this->formatter, $pathToFile);
        $fileHandler->add('name', 'level', 'message');
        $this->assertEquals($expectedContent, \file_get_contents($pathToFile));
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
}
