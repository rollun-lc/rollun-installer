<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 06.01.17
 * Time: 5:27 PM
 */

namespace rolluncom\installer\Install;

use Composer\IO\IOInterface;
use Interop\Container\ContainerInterface;

abstract class InstallerAbstract implements InstallerInterface
{

    /** @var ContainerInterface  */
    protected $container;

    /** @var IOInterface  */
    protected $io;

    /**
     * Installer constructor.
     * @param ContainerInterface $container
     * @param IOInterface $ioComposer
     * @internal param IOInterface $IO
     */
    public function __construct(ContainerInterface $container, IOInterface $ioComposer)
    {
        $this->io = $ioComposer;
        $this->container = $container;
    }

    /**
     * Make clean and install.
     * @return void
     */
    public function reinstall()
    {
        $this->uninstall();
        $this->install();
    }
}