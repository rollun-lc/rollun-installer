<?php

/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 06.01.17
 * Time: 5:27 PM
 */

namespace rollun\installer\Install;

use Composer\IO\IOInterface;
use Exception;
use Interop\Container\ContainerInterface;
use rollun\installer\InstallerException;
use rollun\installer\RootInstaller;

abstract class InstallerAbstract implements InstallerInterface
{

    /** @var ContainerInterface  */
    protected $container;

    /** @var IOInterface  */
    protected $consoleIO;
    /**
     * @var RootInstaller
     */
    private $rootInstaller;

    /**
     * Installer constructor.
     * @param ContainerInterface $container
     * @param IOInterface $ioComposer
     * @internal param IOInterface $IO
     */
    public function __construct(ContainerInterface $container, IOInterface $ioComposer)
    {
        $this->consoleIO = $ioComposer;
        $this->container = $container;
    }

    /**
     * @param RootInstaller $rootInstaller
     */
    public function setRootInstaller(RootInstaller $rootInstaller)
    {
        $this->rootInstaller = $rootInstaller;
    }

    /**
     * Call the installation of the child installer. Return the config it generated.
     * @param $installerName
     * @return array
     */
    protected function callInstaller($installerName)
    {
        if(!isset($this->rootInstaller)) {
            throw new InstallerException("Root installer not injected.");
        }
        return $this->rootInstaller->callInstaller($installerName);
    }

    public function isDefaultOn()
    {
        return false;
    }

    public function getDependencyInstallers()
    {
        return [];
    }

    /**
     * Return installer nameSpace.
     * @return string
     */
    public function getNameSpace()
    {
        $reflection = new \ReflectionClass(static::class);
        return $reflection->getNamespaceName();
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Reask question if aswer is empty
     * @param $question string
     * @return string
     */
    protected function askParams($question)
    {
        do {
            $param = $this->consoleIO->ask($question);
            if (!is_null($param)) {
                break;
            }
            $this->consoleIO->write("Name not valid.");
        } while (true);
        return $param;
    }

    /**
     * Ask question with use default params and question for use.
     * @param $paramName
     * @param $question
     * @param $defaultValue
     * @return string
     */
    protected function askParamWithDefault($paramName, $question, $defaultValue)
    {
        if ($this->consoleIO->askConfirmation("Use the default $paramName for the configuration?")) {
            $param = $defaultValue;
        } else {
            $param = $this->askParams($question);
        }
        return $param;
    }

    /**
     *
     * @param type $question string "Do you want to use default DbAdapter? y/n/q";
     * @param type $default 'y', 'n', 'q' or null
     * @param bool $exitIfQ
     * @return string 'y', 'n', 'q'
     * @throws Exception
     */
    public function askYesNoQuit($question, $default = null, $exitIfQ = true)
    {
        do {
            try {
                $answer = strtolower($this->consoleIO->ask($question));
            } catch (\RuntimeException $exc) {
                if ($exc->getMessage() == 'Aborted') {
                    $answer = 'q';
                } else {
                    throw new Exception($exc->getMessage());
                }
            }

            if (in_array($answer, ['y', 'n', 'q'])) {
                if ($exitIfQ && ($answer == 'q')) {
                    exit;
                }
                return $answer;
            }
            $this->consoleIO->write("Answer is not valid. Type 'y' or 'n' or 'q' (yes/no/quit) pls.");
        } while (true);
    }

}
