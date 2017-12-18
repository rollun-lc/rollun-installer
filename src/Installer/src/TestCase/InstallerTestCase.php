<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 16.01.17
 * Time: 14:13
 */

namespace rollun\installer\TestCase;

use Composer\IO\ConsoleIO;
use PHPUnit\Framework\TestCase;
use rollun\dic\InsideConstruct;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\StreamableInputInterface;

/**
 *
 * @see \rollun\test\installer\Install\InstallerAbstractTest
 */
class InstallerTestCase extends TestCase
{

    public function getContainer()
    {
        global $container;
        $container = isset($container) ? $container : require 'config/container.php';
        InsideConstruct::setContainer($container);
        return $container;
    }

    public function getIo($inputString, $outputStream = null)
    {
        $helperSet = new HelperSet([
            'question' => new QuestionHelper(),
            'formatter' => new FormatterHelper(),
            'descriptor' => new DescriptorHelper(),
            'process' => new ProcessHelper(),
            'debugFormatter' => new DebugFormatterHelper(),
        ]);

        $inputStream = $this->getInputStream($inputString);
        $inputInterfaceMock = $this->createStreamableInputInterfaceMock($inputStream);

        $outputStream = $outputStream ?: $this->getOutputStream();
        $outputStreamInterfaceObiect = $this->createOutputInterface($outputStream);

        $composerIO = new ConsoleIO($inputInterfaceMock, $outputStreamInterfaceObiect, $helperSet);
        return $composerIO;
    }

    public function getOutputStream()
    {
        return fopen('php://memory', 'r+', false);
    }

    protected function createOutputInterface($outputStream)
    {
        return new StreamOutput($outputStream);
    }

    public function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, $input);
        rewind($stream);
        return $stream;
    }

    protected function createStreamableInputInterfaceMock($stream = null, $interactive = true)
    {
        $mock = $this->getMockBuilder(StreamableInputInterface::class)->getMock();
        $mock->expects($this->any())
                ->method('isInteractive')
                ->will($this->returnValue($interactive));
        if ($stream) {
            $mock->expects($this->any())
                    ->method('getStream')
                    ->willReturn($stream);
        }
        return $mock;
    }

}
