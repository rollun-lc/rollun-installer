<?php
/**
 *
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 27.12.16
 * Time: 4:02 PM
 */

namespace rollun\installer;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use FilesystemIterator;
use Interop\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use rollun\installer\Install\InstallerInterface;

require_once 'config/env_configurator.php';

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
     * @param Event $event
     * @return void
     */
    public static function install(Event $event)
    {
        try {
            static::command($event, self::INSTALL);
        } catch (\Exception $exception) {
            $event->getIO()->writeError("Installing error: \n" . $exception->getMessage() . "\nUninstalling changes.");
            static::command($event, self::UNINSTALL);
        }
    }

    /**
     * do command for include installers.
     * Composer Event - for get dependencies and IO
     * @param Event $event
     * Type of command doю
     * @param $commandType
     */
    protected static function command(Event $event, $commandType)
    {
        //founds dep installer only if app
        $composer = $event->getComposer();
        $localRep = $composer->getRepositoryManager()->getLocalRepository();
        //get all dep lis (include dependency of dependency)
        $dependencies = $localRep->getPackages();
        foreach ($dependencies as $dependency) {
            $target = $dependency->getPrettyName();
            //get dependencies and get installer
            $srcPath = $path = realpath('vendor') . DIRECTORY_SEPARATOR .
                $target . DIRECTORY_SEPARATOR .
                'src' . DIRECTORY_SEPARATOR;
            $autoload = $dependency->getAutoload();
            if (isset($autoload['psr-4'])) {
                $namespace = array_keys($autoload['psr-4'])[0];
                $installers = static::getInstallers($namespace, $srcPath);
                static::callInstallers($installers, $commandType, $event->getIO());
            }
        }

        $autoload = $composer->getPackage()->getAutoload();
        if (isset($autoload['psr-4'])) {
            $namespace = array_keys($autoload['psr-4'])[0];
            $installers = static::getInstallers($namespace);
            static::callInstallers($installers, $commandType, $event->getIO());
        }
    }

    protected static function callInstallers(array $installers, $commandType, IOInterface $io)
    {
        /** @var InstallerInterface $installer */
        foreach ($installers as $installerClass) {
            $installer = new $installerClass(self::getContainer(), $io);
            call_user_func([$installer, $commandType]);
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

    /**
     * return array with Install class for lib;
     * dir - for search Installer automate
     * @param $namespace
     * @param string $dir
     * @return InstallerInterface[]
     */
    public static function getInstallers($namespace, $dir = null)
    {
        $installer = [];
        if (!isset($dir)) {
            $dir = realpath('src/');
        }
        try {
            $iterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS |
                FilesystemIterator::KEY_AS_PATHNAME);
        } catch (\Exception $exception) {
            return $installer;
        }

        foreach ($iterator as $item) {
            //Save only class who implement InstallerInterface and has Installer in name
            /** @var $item RecursiveDirectoryIterator */
            if (!preg_match('/^(\.)|(vendor)/', $item->getFilename())) {
                if ($item->isDir()) {
                    $installer = array_merge($installer, static::getInstallers($namespace, $item->getPathname()));
                } elseif (preg_match('/Installer/', $item->getFilename())) {
                    //get path to lib
                    $match = [];
                    $path = preg_match('/\/vendor\/([\w-\/]+)/', $item->getPath(), $match)
                    && isset($match[1]) ? $match[1] : $item->getPath();

                    //get path to src
                    $match = [];
                    $path = preg_match('/\/src\/([\w-\/]+)/', $path, $match)
                    && isset($match[1]) ? $match[1] : null;

                    $namespace_ = $namespace . str_replace(DIRECTORY_SEPARATOR, '\\', $path);
                    $class = rtrim($namespace_, '\\') . '\\' . $item->getBasename('.php');
                    try {
                        $reflector = new \ReflectionClass($class);
                        if ($reflector->implementsInterface(InstallerInterface::class) &&
                            !$reflector->isAbstract() && !$reflector->isInterface()
                        ) {
                            $installer[] = $reflector->getName();
                        }
                    } catch (\ReflectionException $exception) {

                    }
                }
            }
        }
        return $installer;
    }

    /**
     * @return ContainerInterface
     */
    private static function getContainer()
    {
        if (!isset(Command::$container)) {
            Command::$container = include 'config/container.php';
        }

        return Command::$container;
    }

    /**
     * @param Event $event
     * @return void
     */
    public static function uninstall(Event $event)
    {
        static::command($event, self::UNINSTALL);
    }

    /**
     * @param Event $event
     * @return void
     */
    public static function reinstall(Event $event)
    {
        try {
            static::command($event, self::REINSTALL);
        } catch (\Exception $exception) {
            $event->getIO()->writeError("Installing error: \n" . $exception->getMessage() . "\nUninstalling changes.");
            static::command($event, self::UNINSTALL);
        }

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
        return realpath('data/');
    }
}
