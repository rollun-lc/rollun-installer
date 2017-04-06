<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 06.01.17
 * Time: 5:27 PM
 */

namespace rollun\installer\Install;

use Composer\IO\IOInterface;
use Interop\Container\ContainerInterface;

abstract class InstallerAbstract implements InstallerInterface
{

    /** @var ContainerInterface  */
    protected $container;

    /** @var IOInterface  */
    protected $consoleIO;

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

    public function isDefaultOn()
    {
        return false;
    }

    public function getDependencyInstallers()
    {
        return [];
    }

    /**
     * Return true if install, or false else
     * @return bool
     */
    public function isInstall()
    {
        return $this->consoleIO->askConfirmation("You have gone through all the steps to install this " . __CLASS__ . " [Yes/No]", false);
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
}