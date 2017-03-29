<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09.03.17
 * Time: 16:25
 */

namespace rollun\installer;

use Composer\Composer;
use Composer\IO\ConsoleIO;
use rollun\dic\InsideConstruct;
use rollun\installer\Install\InstallerInterface;
use Zend\ServiceManager\ServiceManager;

//todo: добавить транслит для русс на винде
//todo: проверять на корекность данных для подеклбчения к бд
//todo: добавить описание о том как выбирать инталерра

/**
 * Class RootInstaller
 * @package rollun\installer
 */
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
        $this->libInstallerManagers = [];
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

        foreach ($this->installers as $installer) {
            $installer->setContainer($this->container);
        }
        foreach ($this->libInstallerManagers as $libInstallerManager) {
            $libInstallerManager->setContainer($this->container);
        }
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
        $this->libInstallerManagers[] = $libInstallManager = new LibInstallerManager(
            $this->composer->getPackage(),
            $this->container,
            $this->cliIO,
            realpath("src/")
        );
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
        $this->cliIO->write("Start install ...\n");
        foreach ($installers as $installerName) {
            $this->callInstaller($installerName);
        }
        $this->cliIO->write("Finish install - Success.");
    }

    /**
     * @param $lang
     */
    protected function writeDescriptions($lang)
    {
        $lang = substr($lang, 0, 2);
        foreach ($this->installers as $name => $installer) {
            if (!$installer->isInstall()) {
                $description = $installer->getDescription($lang);
                if ($lang === 'ru' && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $description = transliterator_transliterate('Any-Latin;Latin-ASCII;', $description);
                }

                $this->cliIO->write($name . ":\n" . $description);
            }
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

        $installersName = array_keys($selectInstaller);
        foreach ($installersName as $key => $name) {
            $installer = $selectInstaller[$name];
            if ($installer->isDefaultOn()) {
                $defaultInstaller[] = $key;
            }
        }
        if (!empty($selectInstaller)) {
            $selectedInstaller = [];
            $selectedInstallerKey = $this->cliIO->select(
                "Select installer who ben call.Set number separated by `,`. Must be looks like - `0,2,3`",
                $installersName,
                implode(",", $defaultInstaller),
                false,
                'Value "%s" is invalid',
                true);
            if (is_string($selectedInstallerKey)) {
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
                $this->cliIO->write("Start install $installerName:\n");
                try {
                    $config = $installer->install();
                    if (!empty($config)) {
                        $this->generateConfig($config, $installerName);
                        $this->reloadContainer();
                    }
                    $this->cliIO->write("Finish install $installerName - success;\n");
                } catch (\Exception $e) {
                    $this->cliIO->write("Finish install $installerName - exception;\nMessage: " . $e->getMessage());
                    if(!$this->cliIO->askConfirmation("Do you want to continue with the installation?")){
                        $this->cliIO->write("Installation was interrupted and stopped.");
                        exit(0);
                    }
                } catch (\Throwable $e) {
                    $this->cliIO->write("Finish install $installerName - exception;\nMessage: " . $e->getMessage());
                    if(!$this->cliIO->askConfirmation("Do you want to continue with the installation?")){
                        $this->cliIO->write("Installation was interrupted and stopped.");
                        exit(0);
                    }
                }
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
        $fileName = $this->getConfigFileName($installerName);
        $file = fopen($fileName, "w");
        $str = "<?php\nreturn\n" . $this->arrayToString($config) . ";";
        fwrite($file, $str);
    }

    /**
     * Get config file name.
     * @param string $installerName
     * @return string
     */
    protected function getConfigFileName($installerName)
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
        $configName = rtrim($configName, '.');
        return realpath('config/autoload/') . DIRECTORY_SEPARATOR . $libName . $configName . ".dist.local.php";
    }

    /**
     * @param array $array
     * @return string
     */
    protected function arrayToString(array $array)
    {
        static $level;
        $level++;
        $str = "[\n";
        foreach ($array as $key => $item) {
            for ($i = 0; $i < $level; $i++) {
                $str .= "\t";
            }
            if (!is_integer($key)) {
                $str .= "'$key' => ";
            }
            if (is_array($item)) {
                $str .= $this->arrayToString($item);
            } else if (is_integer($item)) {
                $str .= $item;
            } else {
                $str .= "'" . $item . "'";
            }
            $str .= ",\n";
        }
        $str = rtrim($str, ",");
        $level--;
        if (!empty($array)) {
            for ($i = 0; $i < $level; $i++) {
                $str .= "\t";
            }
        }
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
                $installerName = get_class($installer);
                $fileName = $this->getConfigFileName($installerName);
                if (file_exists($fileName)) {
                    unlink($fileName);
                }
                $installer->uninstall();
            }
        }
    }
}
