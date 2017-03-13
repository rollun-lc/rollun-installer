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
use rollun\dic\InsideConstruct;
use rollun\installer\Install\InstallerInterface;
use Zend\ServiceManager\ServiceManager;

class RootInstaller
{
    /** @var  Composer */
    protected $composer;

    /** @var ConsoleIO */
    protected $cliIO;

    /** @var  ServiceManager */
    protected $container;

    /**
     * key - installer name
     * value - installer
     * @var InstallerInterface[]
     */
    protected $installers;

    /** @var  LibInstallerManager[] */
    protected $libInstallerManagers;

    public function __construct(Composer $composer, ConsoleIO $cliIO)
    {
        $this->composer = $composer;
        $this->cliIO = $cliIO;
        $this->installers = [];
        $this->reloadContainer();
        $this->initAllInstallers();
    }

    /**
     * reload config in SM
     */
    private function reloadContainer()
    {

        $this->container = include 'config/container.php';
        InsideConstruct::setContainer($this->container);
    }

    /**
     * init All installers
     */
    protected function initAllInstallers()
    {
        //founds dep installer only if app
        $localRep = $this->composer->getRepositoryManager()->getLocalRepository();
        //get all dep lis (include dependency of dependency)
        $dependencies = $localRep->getPackages();

        foreach ($dependencies as $dependency) {
            $libInstallManager = new LibInstallerManager($dependency, $this->container, $this->cliIO);
            if ($libInstallManager->isSupported()) {
                $this->libInstallerManagers[] = $libInstallManager;
                $this->installers = array_merge($this->installers, $libInstallManager->getInstallers());
            }
        }
        $this->libInstallerManagers[] = $libInstallManager = new LibInstallerManager($this->composer->getPackage(), $this->container, $this->cliIO, realpath("src/"));
        $this->installers = array_merge($this->installers, $libInstallManager->getInstallers());
    }

    /**
     * Call install
     * @param string $lang
     */
    public function install($lang = null)
    {
        $lang = !isset($lang) ? constant("LANG") : $lang;
        $this->writeDescriptions($lang);
        $installers = $this->selectInstaller();
        foreach ($installers as $installerName) {
            $this->callInstaller($installerName);
        }
    }

    /**
     * @param $lang
     */
    protected function writeDescriptions($lang)
    {
        foreach ($this->installers as $name => $installer) {
            $this->cliIO->write($name . ":\n" . $installer->getDescription(substr($lang, 0, 2)));
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
            return !$installer->isInstall();
        });
        foreach ($selectInstaller as $name => $installer) {
            if ($installer->isDefaultOn()) {
                $defaultInstaller[] = $name;
            }
        }
        $installersName = array_keys($selectInstaller);
        if (!empty($selectInstaller)) {
            $selectedInstaller = [];
            $selectedInstallerKey = $this->cliIO->select("Select installer who ben call.", $installersName, implode(",", $defaultInstaller), false, 'Value "%s" is invalid', true);
            if(is_string($selectedInstallerKey)) {
                $selectedInstallerKey = explode(",", $selectedInstallerKey);
            }
            foreach ($selectedInstallerKey as $key) {
                $selectedInstaller[] = $installersName[$key];
            }
            return $selectedInstaller;
        }
        return [];
    }

    /**
     * call selected installer and all dep installers
     * @param $installerName
     */
    protected function callInstaller($installerName)
    {
        if (isset($this->installers[$installerName])) {
            $installer = $this->installers[$installerName];
            if (!$installer->isInstall()) {
                $dependencyInstallers = $installer->getDependencyInstallers();
                foreach ($dependencyInstallers as $depInstaller) {
                    $this->callInstaller($depInstaller);
                }
                $config = $installer->install();
                $this->generateConfig($config, $installerName);
                $this->reloadContainer();
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
            if ($installerManager->getInstaller($installerName) !== null) {
                $libName = str_replace("\\", ".", $installerManager->getRootNamespace());
                break;
            }
        }
        $match = [];
        $configName = preg_match('/([\w]+)Installer$/', $installerName, $match) ? $match[1] : "";
        $fileName = realpath('config/autoload/') . DIRECTORY_SEPARATOR . $libName . $configName . "dist.local.php";
        $file = fopen($fileName, "w");
        $str = "<?php\nreturn " . $this->arrayToString($config) . ";";
        fwrite($file, $str);
    }

    /**
     * @param array $array
     * @return string
     */
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
            $str .= ",";
        }
        $str = rtrim($str, ",");
        $str .= "]";
        return $str;
    }

    /**
     * Call uninstall
     */
    public function uninstall()
    {
        foreach ($this->installers as $installer) {
            if ($installer->isInstall()) {
                $installer->uninstall();
            }
        }
    }
}
