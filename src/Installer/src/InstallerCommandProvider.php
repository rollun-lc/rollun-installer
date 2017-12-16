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

/**
 * @deprecated
 */
if(file_exists('vendor/webimpress/http-middleware-compatibility/autoload/http-middleware.php')) {
    require_once 'vendor/webimpress/http-middleware-compatibility/autoload/http-middleware.php';
}
if(file_exists('config/env_configurator.php')) {
    require_once 'config/env_configurator.php';
}

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

		//TODO: remove this gilt. Need for support back compatibility.
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