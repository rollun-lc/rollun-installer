<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 15.03.18
 * Time: 11:43 AM
 */

namespace rollun\installer\Example;


use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use rollun\installer\Install\InstallerAbstract;
use rollun\installer\InstallerException;

class ExampleSelectInstaller extends InstallerAbstract
{

    /*
     * In old version composer return key of the ansver.
     *
     */
    /**
     * @var array
     */
    protected $selectChoices = ["val1","val2","val3","val4","val5"];

    /**
     * install
     * @return array
     */
    public function install()
    {
        $ansver = $this->consoleIO->select(
            "Select anyone val",
            $this->selectChoices,
            "val1"
        );
        $this->consoleIO->write("ansver:".print_r($ansver,true));

        return [
            ExampleSelectInstaller::class => $this->selectChoices[$ansver]
        ];
    }

    /**
     * Clean all installation
     * @return void
     */
    public function uninstall()
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * Return string with description of installable functional.
     * @param string $lang ; set select language for description getted.
     * @return string
     */
    public function getDescription($lang = "en")
    {
        return "Test installer";
    }

    /**
     * Return true if install, or false else
     * @return bool
     */
    public function isInstall()
    {
        try {
            $config = $this->container->get("config");
        } catch (NotFoundExceptionInterface $e) {
            throw new InstallerException("Exception by run isInstall.", $e->getCode(), $e);
        } catch (ContainerExceptionInterface $e) {
            throw new InstallerException("Exception by run isInstall.", $e->getCode(), $e);
        }
        return (
            isset($config[ExampleSelectInstaller::class]) &&
            $config[ExampleSelectInstaller::class]
        );
    }
}