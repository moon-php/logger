<?php

declare(strict_types=1);

namespace Moon\Logger\Unit;

use Moon\Logger\Handler\HandlerInterface;
use Moon\Logger\Logger;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;

class LoggerTest extends TestCase
{
    /**
     * Mock the filesystem on setup.
     */
    public static function setUpBeforeClass()
    {
        vfsStream::setup('home/');
    }

    /**
     * Test that logger name has been set.
     *
     * @dataProvider loggerNameDataProvider
     */
    public function testLoggerNameBeingSet($name)
    {
        $handler = $this->getMockBuilder(HandlerInterface::class)->disableOriginalConstructor()->getMock();
        $logger = new Logger($name, [$handler]);

        $reflectionLogger = new \ReflectionClass($logger);
        $loggerName = $reflectionLogger->getProperty('name');
        $loggerName->setAccessible(true);

        $this->assertEquals($loggerName->getValue($logger), $name);
    }

    /**
     * Test that all handler has been set.
     *
     * @dataProvider invalidLoggerHandlersDataProvider
     */
    public function testLoggerHandlersBeingSet($invalidHandlers)
    {
        $this->expectException(InvalidArgumentException::class);
        new Logger('', $invalidHandlers);
    }

    /**
     * Test that all the available log level can be called.
     *
     * @dataProvider loggerMethodDataProvider
     */
    public function testExistingMethods($method)
    {
        $this->assertTrue(\method_exists(Logger::class, $method));
    }

    /**
     * Test that only the available log level can be called.
     *
     * @dataProvider loggerInvalidMethodDataProvider
     */
    public function testInvalidMethods($method)
    {
        $this->expectException(InvalidArgumentException::class);
        $handler = $this->getMockBuilder(HandlerInterface::class)->disableOriginalConstructor()->getMock();
        $logger = new Logger('', [$handler]);
        $logger->log($method, '');
    }

    /**
     * Test that InvalidArgumentException is thrown on invalid message.
     *
     * @dataProvider loggerInvalidMessageDataProvider
     */
    public function testInvalidMessage($message)
    {
        $this->expectException(InvalidArgumentException::class);
        $handler = $this->getMockBuilder(HandlerInterface::class)->disableOriginalConstructor()->getMock();
        $logger = new Logger('', [$handler]);
        $logger->debug($message);
    }

    /**
     * Test that no exception is thrown with a valid message.
     *
     * @dataProvider loggerValidMessageDataProvider
     */
    public function testValidMessage($message)
    {
        $handler = $this->getMockBuilder(HandlerInterface::class)->disableOriginalConstructor()->getMock();
        $logger = new Logger('', [$handler]);
        $logger->debug($message);
        $this->assertTrue(true);
    }

    /**
     * Return logger names.
     */
    public function loggerNameDataProvider(): array
    {
        return [
            ['name'],
            ['123'],
            [''],
        ];
    }

    /**
     * Return invalid Handlers.
     */
    public function invalidLoggerHandlersDataProvider(): array
    {
        return [
            [[]],
            [['string'], [new \stdClass()]],
        ];
    }

    /**
     * Return valid log level.
     */
    public function loggerMethodDataProvider(): array
    {
        return [
            ['log'],
            ['emergency'],
            ['alert'],
            ['critical'],
            ['error'],
            ['warning'],
            ['notice'],
            ['info'],
            ['debug'],
        ];
    }

    /**
     * Return invalid log level.
     */
    public function loggerInvalidMethodDataProvider(): array
    {
        return [
            ['log'],
            ['not_exists'],
            ['invalid'],
        ];
    }

    /**
     * Return invalid messages.
     */
    public function loggerInvalidMessageDataProvider(): array
    {
        return [
            [new \SplObjectStorage()],
            [['array']],
        ];
    }

    /**
     * Return valid messages.
     */
    public function loggerValidMessageDataProvider(): array
    {
        $fake = $this->getMockBuilder('fakeClass')
            ->setMockClassName('Fake')
            ->setMethods(['__toString'])
            ->getMock();

        return [
            ['This is a fake message', $fake],
        ];
    }
}
