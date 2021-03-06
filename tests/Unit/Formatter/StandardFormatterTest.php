<?php

declare(strict_types=1);

namespace Moon\Logger\Unit\Formatter;

use Moon\Logger\Formatter\StandardFormatter;
use PHPUnit\Framework\TestCase;

class StandardFormatterTest extends TestCase
{
    public function testNewDateFormatHasBeenSet()
    {
        $formatter = new StandardFormatter('Y-m-d');
        $reflection = new \ReflectionObject($formatter);
        $defaultCurrentTime = $reflection->getProperty('dateFormat');
        $defaultCurrentTime->setAccessible(true);
        $this->assertSame('Y-m-d', $defaultCurrentTime->getValue($formatter));
    }

    public function testGetTimestampFromImmutableReturnAValidFormat()
    {
        $formatter = new StandardFormatter('i');
        $reflection = new \ReflectionObject($formatter);
        $getTimestampFromImmutable = $reflection->getMethod('getTimestampFromImmutable');
        $getTimestampFromImmutable->setAccessible(true);
        $seconds = $getTimestampFromImmutable->invoke($formatter);
        $this->assertRegExp('/^[0-5][0-9]$/', $seconds);
    }

    /**
     * Test interpolation validity.
     *
     * @dataProvider messageDataProvider
     */
    public function testInterpolation($name, $level, $message, array $context, $regex)
    {
        // Override the getTimestampFromImmutable method for mock the log's timestamp
        $formatter = $this->createPartialMock(StandardFormatter::class, ['getTimestampFromImmutable']);
        $formatter->expects($this->any())->method('getTimestampFromImmutable')->will($this->returnValue('DATA'));

        /** @var StandardFormatter $formatter */
        $interpolated = $formatter->interpolate($name, $level, $message, $context);

        $interpolated = \str_replace(\realpath(__DIR__) ?: '', '', $interpolated);
        $this->assertRegExp($regex, $interpolated);
    }

    /**
     * Return a list of log params and the last is a regex.
     */
    public function messageDataProvider()
    {
        $exception = new \Exception('first', 1, new \Exception('second', 2, new \Exception('third', 3)));

        return [
            ['name', 'level', 'message', [], '#\[DATA\] (\w+).(\w+): (.)+ (\[\]) \[\]#'],
            [
                'name',
                'level',
                'Custom message: {message}',
                ['message' => 'hello'],
                '#\[DATA\] (\w+).(\w+): Custom message: hello (\[\]) \[\]#',
            ],
            [
                'name',
                'level',
                'fake exception',
                ['exception' => 'fake'],
                '#\[DATA\] (\w+).(\w+): (.)+ \[({"exception":"fake"})\] \[\]#',
            ],
            [
                'name',
                'level',
                'true exception',
                ['exception' => $exception],
                '#\[DATA\] (\w+).(\w+): (.)+ (\[\]) \[(,?\( Exception: \(code:  [123] \):  (first|second|third)  at  ([\w/]+).php : \d+\)){3}\]#',
            ],
        ];
    }
}
