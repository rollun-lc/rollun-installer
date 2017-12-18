<?php
/**
 *
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 27.12.16
 * Time: 4:02 PM
 */
//Instalabe StaticInstalabe
namespace rollun\installer;

use Composer\Command\BaseCommand;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use FilesystemIterator;
use Interop\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use rollun\dic\InsideConstruct;
use rollun\installer\Install\InstallerInterface;

/**
 * Class Command
 * @package rollun\installer
 * @deprecated see InstallerCommand::class
 */
class Command
{
	const INSTALL = 'install';

    const UNINSTALL = 'uninstall';

    const REINSTALL = 'reinstall';

    /**
     * avz-cmf [lib-name] => [
     *      "class" => 'InstallerCommands::Class'
     *      "installed" => true|false
     * ]
     * @var array
     **/
    protected static $dep = [];

    /** @var ContainerInterface */
    private static $container = null;

    /**
     * do command for include installers.
     * Composer Event - for get dependencies and IO
     * @param Event $event
     */
    public static function command(Event $event)
    {
        /*
         * usage: lib
         *  [ install | uninstall ]
         *  [ -l= ]
         */
        $argv = $event->getArguments();
        $match = [];
        $lang = preg_match('/-l=([\w]+)\|?/', implode("|", $argv), $match) ? $match[1] : null;
        $isDebug = in_array("debug", $argv) ? true : false;
        define("isDebug", $isDebug);//TODO: refactor this.
        /** @noinspection PhpParamsInspection */
        try {
            $rootInstaller = new RootInstaller($event->getComposer(), $event->getIO());
            if(in_array('install', $argv)){
                $rootInstaller->install($lang);
            } else if (in_array('uninstall', $argv)){
                $rootInstaller->uninstall();
            } else {
                $event->getIO()->writeError("usage:\n composer lib [install\\uninstall] [-l={language}]");
            }
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    /**
     * Return true if call in lib or false in app
     * @return string
     */
    public static function isLib()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $className = $trace[1]['class'];
        $reflectionClass = new \ReflectionClass($className);
        return preg_match('/\/vendor\//', $reflectionClass->getFileName()) == 1;
    }

    public static function getPublicDir()
    {
        /**
         * Have a list of names of public directories.
         * Iterate through the directory to check their availability and presence in her file index.php
         */
        $publicDirs = [
            'www',
            'public',
            'web',
        ];
        foreach ($publicDirs as $publicDir) {
            if (is_dir($publicDir) && file_exists($publicDir . DIRECTORY_SEPARATOR . "index.php")) {
                return realpath($publicDir);
            }
        }
        throw new \Exception("The public directory was not found");
    }

    public static function getDataDir()
    {
        return realpath('./') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    }
}
