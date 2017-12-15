<?php
/**
 * Created by PhpStorm.
 * User: victorynox
 * Date: 15.12.17
 * Time: 17:44
 */

namespace rollun\installer;


use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\PluginInterface;

class InstallerCommandProvider implements CommandProvider, PluginInterface
{


    /**
     * Retreives an array of commands
     *
     * @return \Composer\Command\BaseCommand[]
     */
    public function getCommands()
    {
        return [new InstallerCommand()];
    }

    /**
     * Apply plugin modifications to Composer
     *
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $io->write("hello ");
    }
}