<?php

namespace Moon\Logger\Unit\Formatter;


use Moon\Logger\Formatter\StandardFormatter;

class StandardFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test interpolation validity
     *
     * @param string $name
     * @param string $level
     * @param string $message
     * @param array $context
     * @param string $regex Has # as delimiter
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

        $interpolated = str_replace(realpath(dirname(__FILE__)), '', $interpolated);
        $this->assertRegExp($regex, $interpolated);
    }

    /**
     * Return a list of log params and the last is a regex
     *
     * @return array
     */
    public function messageDataProvider()
    {
        $exception = new \Exception('first', 1, new \Exception('second', 2, new \Exception('third', 3)));

        return [
            ['name', 'level', 'message', [], '#\[DATA\] (\w+).(\w+): (.)+ (\[\]) \[\]#'],
            ['name', 'level', 'Custom message: {message}', ['message' => 'hello'], '#\[DATA\] (\w+).(\w+): Custom message: hello (\[\]) \[\]#'],
            ['name', 'level', 'fake exception', ['exception' => 'fake'], '#\[DATA\] (\w+).(\w+): (.)+ \[({"exception":"fake"})\] \[\]#'],
            ['name', 'level', 'true exception', ['exception' => $exception], '#\[DATA\] (\w+).(\w+): (.)+ (\[\]) \[(,?\( Exception: \(code:  [123] \):  (first|second|third)  at  ([\w/]+).php : \d+\)){3}\]#'],
        ];
    }
}