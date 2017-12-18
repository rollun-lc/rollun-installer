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
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;


class InstallerCommandProvider implements CommandProvider, PluginInterface, Capable
{

	/**
	 * @var Composer
	 */
	protected $composer;

	/**
	 * @var IOInterface
	 */
	protected $io;

	/**
     * Apply plugin modifications to Composer
     *
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
		$this->composer = $composer;
		$this->io = $io;

        if(file_exists('config/env_configurator.php')) {
            require_once 'config/env_configurator.php';
            trigger_error("This functional is deprecated. You may use config for this. For more info read https://github.com/rollun-com/all-standards", E_USER_DEPRECATED);
        }

		// Generate custom autoload witch can load all class.
        // Has compose all autoload script/loader from root and dependency packages.
        // Need if dependency has self custom autoload, like for example in **webimpress/http-middleware-compatibility**
        $localRepository = $composer->getRepositoryManager()->getLocalRepository();
        $packageMap = $composer->getAutoloadGenerator()->buildPackageMap(
            $composer->getInstallationManager(), //Composer InstallationManager object
            $composer->getPackage(), //Composer root package object
            $localRepository->getPackages() //Array of dependency package
        );
        // Generate array with all packages autoload info.
        $autoloads = $composer->getAutoloadGenerator()->parseAutoloads($packageMap, $composer->getPackage());
        // Create custom loader.
        $loader = $composer->getAutoloadGenerator()->createLoader($autoloads);
        // Register loader
        spl_autoload_register([$loader, "loadClass"]);
    }

	/**
	 * Retreives an array of commands
	 *
	 * @return \Composer\Command\BaseCommand[]
	 */
	public function getCommands()
	{
		return [new InstallerCommand];
	}

	/**
	 * Method by which a Plugin announces its API implementations, through an array
	 * with a special structure.
	 *
	 * The key must be a string, representing a fully qualified class/interface name
	 * which Composer Plugin API exposes.
	 * The value must be a string as well, representing the fully qualified class name
	 * of the implementing class.
	 *
	 * @tutorial
	 *
	 * return array(
	 *     'Composer\Plugin\Capability\CommandProvider' => 'My\CommandProvider',
	 *     'Composer\Plugin\Capability\Validator'       => 'My\Validator',
	 * );
	 *
	 * @return string[]
	 */
	public function getCapabilities()
	{
		return [CommandProvider::class => static::class];
	}
}