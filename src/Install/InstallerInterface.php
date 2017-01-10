<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 26.12.16
 * Time: 5:03 PM
 */

namespace rolluncom\installer\Install;

use Composer\IO\IOInterface;
use Interop\Container\ContainerInterface;

interface InstallerInterface
{
    /**
     * Installer constructor.
     * @param ContainerInterface $container
     * @param IOInterface $ioComposer
     * @internal param IOInterface $IO
     */
    public function __construct(ContainerInterface $container, IOInterface $ioComposer);

    //TODO: init and re must make clean if during installation exception was obtained.
    /**
     * install
     * @return void
     */
    public function install();

    //TODO: The method clean should be finish work without exceptions
    /**
     * Clean all installation
     * @return void
     */
    public function uninstall();

    /**
     * Make clean and install.
     * @return void
     */
    public function reinstall();
}