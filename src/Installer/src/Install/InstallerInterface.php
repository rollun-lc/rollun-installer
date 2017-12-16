<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 26.12.16
 * Time: 5:03 PM
 */

namespace rollun\installer\Install;

use Composer\IO\IOInterface;
use Interop\Container\ContainerInterface;
use rollun\installer\RootInstaller;

interface InstallerInterface
{
    /**
     * Installer constructor.
     * @param ContainerInterface $container
     * @param IOInterface $ioComposer
     * @internal param IOInterface $IO
     */
    public function __construct(ContainerInterface $container, IOInterface $ioComposer);

    /**
     * @param RootInstaller $rootInstaller
     * @return void
     */
    public function setRootInstaller(RootInstaller $rootInstaller);

    //TODO: init and re must make clean if during installation exception was obtained.
    /**
     * install
     * @return array
     */
    public function install();

    //TODO: The method clean should be finish work without exceptions
    /**
     * Clean all installation
     * @return void
     */
    public function uninstall();

    /**
     * Return true if install, or false else
     * @return bool
     */
    public function isInstall();

    /**
     * Return true if recommended to install.
     * @return bool
     */
    public function isDefaultOn();

    /**
     * Return string with description of installable functional.
     * @param string $lang; set select language for description getted.
     * @return string
     */
    public function getDescription($lang = "en");

    /**
     * Return array of dependency installers.
     * They be call before current installers.
     * @return string[]
     */
    public function getDependencyInstallers();

    /**
     * Set container.
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Return installer nameSpace.
     * @return string
     */
    public function getNameSpace();
}