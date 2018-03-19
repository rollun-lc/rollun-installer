<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 10.12.17
 * Time: 1:51 PM
 */

namespace rollun\installer\Example;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use rollun\installer\Install\InstallerAbstract;
use rollun\installer\InstallerException;

class ExampleOneInstaller extends InstallerAbstract
{

    /**
     * install
     * @return array
     */
    public function install()
    {
        return [
            ExampleOneInstaller::class => true
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
        } catch (ContainerExceptionInterface $e){
            throw new InstallerException("Exception by run isInstall.", $e->getCode(), $e);
        }
        return (
            isset($config[ExampleOneInstaller::class]) &&
            $config[ExampleOneInstaller::class] === true
        );
    }
}