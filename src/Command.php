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

use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use FilesystemIterator;
use Interop\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use rollun\dic\InsideConstruct;
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
        $argv = $event->getArguments();
        try {
            static::command($event, static::INSTALL, (isset($argv[0]) ? $argv[0] : null));
        } catch (\Exception $exception) {
            $event->getIO()->writeError("Installing error: \n" . $exception->getMessage() . "\nUninstalling changes.");
            static::command($event, static::UNINSTALL, (isset($argv[0]) ? $argv[0] : null));
        }
    }

    /**
     * do command for include installers.
     * Composer Event - for get dependencies and IO
     * @param Event $event
     * Type of command doю
     * @param $commandType
     * @param null $libName
     */
    protected static function command(Event $event, $commandType, $libName = null)
    {
        //Key => namespace
        //value  => installer
        $allInstallers = [];

        //founds dep installer only if app
        $composer = $event->getComposer();
        $localRep = $composer->getRepositoryManager()->getLocalRepository();
        //get all dep lis (include dependency of dependency)
        $dependencies = $localRep->getPackages();
        foreach ($dependencies as $dependency) {
            $depInstallers = static::getInstallers($dependency, $event, $libName);
            $allInstallers = array_merge($allInstallers, $depInstallers);
        }
        $allInstallers = array_merge($allInstallers,
            static::getInstallers($composer->getPackage(), $event, $libName));

        $cliIO  = $event->getIO();

        /** @var InstallerInterface $installer */
        foreach ($allInstallers as $name => $installer) {
            $description = $installer->getDescription();
            $str = $name . ":\n" . $description;
            $cliIO->write($str, true);
        }
    }


    /**
     * @param PackageInterface $package
     * @param Event $event
     * @param null $libName
     * @return array
     */
    protected static function getInstallers(PackageInterface $package, Event $event, $libName = null)
    {
        $installersObj = [];
        $autoload = $package->getAutoload();
        if (!isset($libName) || $package->getPrettyName() == $libName) {
            if (isset($autoload['psr-4'])) {
                $namespace = array_keys($autoload['psr-4'])[0];
                $src = $autoload['psr-4'][$namespace];
                if (!empty($src) && is_string($src)) {
                    $srcPath = realpath('vendor') . DIRECTORY_SEPARATOR .
                        $package->getPrettyName() . DIRECTORY_SEPARATOR . $src;
                    $srcPath = ($package === $event->getComposer()->getPackage()) ? realpath('src/') : $srcPath;
                    if (is_dir($srcPath)) {
                        $installers = static::findInstallers($namespace, $srcPath);
                        foreach ($installers as $installerClass) {
                            try {
                                $installer = new $installerClass(static::getContainer(), $event->getIO());
                                $installersObj[$installerClass] = $installer;
                                //call_user_func([$installer, $commandType]);
                            } catch (\Exception $exception) {
                                $event->getIO()->writeError(
                                    "Installer: $installerClass crash by exception with message: " .
                                    $exception->getMessage()
                                );
                            }
                        }
                    }
                }
            }
        }
        return $installersObj;
    }

    /**
     * return array with Install class for lib;
     * dir - for search Installer automate
     * @param $namespace
     * @param string $dir
     * @return string[]
     */
    protected static function findInstallers($namespace, $dir)
    {
        $installer = [];

        if (is_dir($dir)) {
            $iterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS |
                FilesystemIterator::KEY_AS_PATHNAME);
        } else {
            return $installer;
        }

        foreach ($iterator as $item) {
            //Save only class who implement InstallerInterface and has Installer in name
            /** @var $item RecursiveDirectoryIterator */
            if (!preg_match('/^(\.)|(vendor)/', $item->getFilename())) {
                if ($item->isDir()) {
                    $installer = array_merge($installer, static::findInstallers($namespace, $item->getPathname()));
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
                    if (class_exists($class)) {
                        $reflector = new \ReflectionClass($class);
                        if ($reflector->implementsInterface(InstallerInterface::class) &&
                            $reflector->isInstantiable()
                        ) {
                            $installer[] = $reflector->getName();
                        }
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
        if (!isset(static::$container)) {
            static::$container = include 'config/container.php';
            InsideConstruct::setContainer(static::$container);
        }

        return static::$container;
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
     * @param Event $event
     * @return void
     */
    public static function uninstall(Event $event)
    {
        $argv = $event->getArguments();
        static::command($event, static::UNINSTALL, (isset($argv[0]) ? $argv[0] : null));
    }

    /**
     * @param Event $event
     * @return void
     */
    public static function reinstall(Event $event)
    {
        $argv = $event->getArguments();

        try {
            static::command($event, static::REINSTALL, (isset($argv[0]) ? $argv[0] : null));
        } catch (\Exception $exception) {
            $event->getIO()->writeError("Installing error: \n" . $exception->getMessage() . "\nUninstalling changes.");
            static::command($event, static::UNINSTALL, (isset($argv[0]) ? $argv[0] : null));
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
        return realpath('./') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    }
}
