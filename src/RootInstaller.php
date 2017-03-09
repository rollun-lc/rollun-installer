<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09.03.17
 * Time: 16:25
 */

namespace rollun\installer;

use Composer\Composer;
use Composer\Installer;
use Composer\IO\ConsoleIO;
use Composer\Repository\WritableRepositoryInterface;
use FilesystemIterator;
use Interop\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use rollun\installer\Install\InstallerInterface;

class RootInstaller
{
    /** @var  Composer */
    protected $composer;

    /** @var ConsoleIO */
    protected $cliIO;

    /** @var  ContainerInterface */
    protected $container;

    /**
     * key - installer name
     * value - installer
     * @var InstallerInterface[]
     */
    protected $installers;

    /** @var  LibInstallerManager[] */
    protected $libInstallerManagers;

    public function __construct(Composer $composer, ContainerInterface $container, ConsoleIO $cliIO)
    {
        $this->composer = $composer;
        $this->container = $container;
        $this->cliIO = $cliIO;
    }

    protected function initAllInstallers()
    {
        //founds dep installer only if app
        $localRep = $this->composer->getRepositoryManager()->getLocalRepository();
        //get all dep lis (include dependency of dependency)
        $dependencies = $localRep->getPackages();

        foreach ($dependencies as $dependency) {
            $libInstallerManagers[] = $libInstallManager = new LibInstallerManager($dependency, $this->container, $this->cliIO);
            $this->installers = array_merge($this->installers, $libInstallManager->getInstallers());
        }
        $libInstallerManagers[] = $libInstallManager = new LibInstallerManager($this->composer->getPackage(), $this->container, $this->cliIO, realpath("src/"));
        $this->installers = array_merge($this->installers, $libInstallManager->getInstallers());
    }

    protected function writeDescriptions()
    {
        foreach ($this->installers as $name => $installer) {
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $name = '\033[0;31m' . $name . '\033[0m';
            }
            $this->cliIO->write($name . ":\n" . $installer->getDescription());
        }
    }

    /**
     * return array with name of selected installer.
     * @return string[]
     */
    protected function selectInstaller()
    {
        $defaultInstaller = [];
        $selectInstaller = array_filter($this->installers, function (InstallerInterface $installer) {
            return $installer->isInstall();
        });
        foreach ($selectInstaller as $name => $installer) {
            if($installer->isDefaultOn()) {
                $defaultInstaller[] = $name;
            }
        }
        $result = $this->cliIO->select("Select installer who ben call.", array_keys($selectInstaller), implode(",",$defaultInstaller));
        return explode(",", $result);
    }

    protected function callInstaller($installerName)
    {
        if(isset($this->installers[$installerName])) {
            $installer = $this->installers[$installerName];
            if(!$installer->isInstall()) {
                $dependencyInstallers = $installer->getDependencyInstallers();
                foreach ($dependencyInstallers as $depInstaller) {
                    $this->callInstaller($depInstaller);
                }
                $config = $installer->install();
            }
        } else {
            throw new \RuntimeException("Installer with name $installerName not found.");
        }
    }

    /**
     * @param array $config
     * @param $installerName
     */
    protected function generateConfig(array $config, $installerName)
    {
        $libName = "";
        foreach ($this->libInstallerManagers as $installerManager) {
            if($installerManager->getInstaller($installerName) !== null) {
                $libName = str_replace("\\", ".", $installerManager->getRootNamespace());
                break;
            }
        }
        $fileName = realpath('config/autoload/') . $libName . '.' . basename($installerName) .".dist.local.php";
        $file = fopen($fileName, "w");
        $str = "<?php\nreturn " . $this->arrayToString($config);
        fwrite($file, $str);
    }

    protected function arrayToString(array $array)
    {
        $str = "[";
        foreach ($array as $key => $item) {
            $str .= "'$key' => ";
            if (is_array($item)) {
                $str .= $this->arrayToString($item);
            } else {
                $str .= "'" . $item . "'";
            }
            $str .= ",\n";
        }
        $str = rtrim($str, ",\n");
        $str .= "\n];";
        return $str;
    }
}
