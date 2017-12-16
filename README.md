## rollun-installer

---
## [Оглавление](https://github.com/rollun-com/rollun-skeleton/blob/master/docs/Contents.md)

---

* [README InstallerSelfCall](https://github.com/rollun-com/rollun-installer/blob/master/docs/InstallerSelfCall.md)

## Документация rollun-installer

Библиотека install позволяет произвести настройку окружение для вашей библиотеки или приложения.

Вы должны создать реализации интерфейса `InstallerInterface` в которых и будет описана процедура настройки окружения.
Данные реализации обязаны содержать в себе суфикс `Installer`.

Так же существует `InstallerAbstract` абстрактная реализация интерфейса.
При ее использоваии вам нужно реализовать обязательные метды `install`, `uninstall`, `isInstall`, `getDescription`.
* `install` - Установить данный инсталлер,
и вернет конфиг который нужно добавить для работы данного инсталлера.
* `uninstall` - Удалить все созданное инсталятором.
* `isInstall` - Возвращяет `true` в случае если данный инсталлер был установлен, и `false` в ином случае.
* `getDescription` - Выводит на консоль описание данного инсталлера.
Есть еще перечень дополнительные методов:
* `isDefaultOn` - Возвращает `true` в случае если данный инсталлер рекомендован к установке.
* `getDependencyInstallers` - Возвращает список зависимых инсталлеров для данного инсталлера.
> Инсталлера из данного списка будут запущены раньше основного инсталлера.

При запуске инсталяции, будут найдены все подобные инсталлеры в порядке **сверху вниз**.
> Учитывайте это при напсании своих инсталлеров если вам важен порядок их вызова.

Данная библиотека позволяет настраивать окружение для зависимых библиотек.

Сначало будут обрабатыватся инсталлеры зависимых библиотек, а в конце ваши.

#### Вызов инсталяции в рантайме.

Так же иснталлер имеет возможность во время своей работы, вызвать установку другого иснталлера.
Для этого используйте метод `InstallerAbstract::callInstaller($installerName)`, передав ему в парметры, имя желаемого инсталлера.
> В качестве имени инсталлера используеться его **className**, то есть для инсталлера **ExampleOneInstaller** **className** - `rollun\installer\Example\ExampleOneInstaller::class`.

Даный метод вернет массив-конфиг сгенерированый вызываемым инсталлером, либо пустой массив, в случае если инсталлер уже был установлен ранее.

### Пример

Пример инсталлера который создает требуемый для приложения файл

```php
<?php
    /**
     * Created by PhpStorm.
     * User: victorsecuring
     * Date: 30.12.16
     * Time: 2:16 PM
     */
    namespace rollun\logger;
    use Composer\IO\IOInterface;
    use Interop\Container\ContainerInterface;
    use rollun\installer\Command;
    use rollun\installer\Install\InstallerAbstract;
    use rollun\logger\Factory\LoggingErrorListenerDelegatorFactory;
    use rollun\logger\LogWriter\FileLogWriter;
    use rollun\logger\LogWriter\FileLogWriterFactory;
    use rollun\logger\LogWriter\LogWriterInterface;
    use Zend\Stratigility\Middleware\ErrorHandler;

    class LoggerInstaller extends InstallerAbstract
    {
        const LOGS_DIR = 'logs';
        const LOGS_FILE = 'logs.csv';
        /**
         * Make clean and install.
         * @return void
         */
        public function reinstall()
        {
            $this->uninstall();
            $this->install();
        }
        /**
         * Clean all installation
         * @return void
         */
        public function uninstall()
        {
            if (constant('APP_ENV') !== 'dev') {
                $this->consoleIO->write('constant("APP_ENV") !== "dev" It has did nothing');
            } else {
                $publicDir = Command::getDataDir();
                if (file_exists($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR . DIRECTORY_SEPARATOR . self::LOGS_FILE)) {
                    unlink($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR . DIRECTORY_SEPARATOR . self::LOGS_FILE);
                }
                if (is_dir($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR)) {
                    rmdir($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR);
                }
            }
        }
        /**
         * install
         * @return array
         */
        public function install()
        {
            if (constant('APP_ENV') !== 'dev') {
                $this->consoleIO->write('constant("APP_ENV") !== "dev" It has did nothing');
            } else {
                $dir = Command::getDataDir() . DIRECTORY_SEPARATOR . self::LOGS_DIR;
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                $file = $dir . DIRECTORY_SEPARATOR . self::LOGS_FILE;
                fopen($file, "w");
                file_put_contents($file, "id;level;message\n");
                return [
                    'dependencies' => [
                        'factories' => [
                            FileLogWriter::class => FileLogWriterFactory::class,
                            Logger::class => LoggerFactory::class,
                        ],
                        'aliases' => [
                            LogWriterInterface::DEFAULT_LOG_WRITER_SERVICE => FileLogWriter::class,
                            Logger::DEFAULT_LOGGER_SERVICE => Logger::class,
                        ],
                        'delegators' => [
                            ErrorHandler::class => [
                                LoggingErrorListenerDelegatorFactory::class
                            ]
                        ]
                    ]
                ];
            }
        }
        public function isInstall()
        {
            $publicDir = Command::getDataDir();
            $result = file_exists($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR . DIRECTORY_SEPARATOR . self::LOGS_FILE);
            $result &= $this->container->has(LogWriterInterface::DEFAULT_LOG_WRITER_SERVICE);
            $result &= $this->container->has(Logger::DEFAULT_LOGGER_SERVICE);
            return $result;
        }
        public function isDefaultOn()
        {
            return true;
        }
        public function getDescription($lang = "en")
        {
            switch ($lang) {
                case "ru":
                    $description = "Предоставяляет обьект logger позволяющий писать сообщения в лог.\n" .
                        "LoggerException которое позволяет записывать в лог возникшее исключение, а так же предшествующее ему.";
                    break;
                default:
                    $description = "Does not exist.";
            }
            return $description;
        }
    }
    ?>
```

## Переменные окружения

Для обозначения типа рабочего окружения используется переменная окружения `APP_EVN`
Используйте ее что бы определять для какого окружение происходит настройка.

Так же должны быть переменная `MACHINE_NAME` которая должна содержать в себе имя текущей контейнера/машины.
Она должна содержать имя в таком виде  `{server_name}-{vm_name}-{container_name}`.
В случае если одной составляющей из данной цепочи не существует - пропустите ее.
Пример:
* `ServerDrakon-ProductionVM-5000`
* `ServerDrakon-ProductionVM-`
* `ServerDrakon--5000`
* `ServerDrakon--`


## Запуск установщиков

Для того что бы можно было запускать инсталлеры используя композер вы должны установить **rollun-com/rollun-installer** в качестве зависимости.

Теперь после того как все предыдущее шаги были сделаны, вы можете используя команды
* `composer lib install` - Запускать инсталяторы для настрройки окружения.
* `composer lib uninstall` - Удалять настроки окружения.
Так же существует необязательный параметр `-l=` которым можно указать язык на котором будет выводится описание
* `composer lib install -l=ru` - Запускать инсталяторы, описание выводить на русском языке.
* Так же для удобства существует флаг ` debug` - он выведет всю информацию о найденый и
проаналезированых библиотеках в котором не нашел инсталлера. Подобные данные могут помочь в отладке.
Пример использования `composer lib install debug`.

## Composer\IO\IOInterface

Для удобства использования, созданы вспомогательные функции

* `InstallerAbstract::askParams` - будет повторять вопрос и сообщать об ошибке пока ответ пустой
* `InstallerAbstract::askParamWithDefault`- спросить пользователя хочет ли он использовать значение по умолчанию если ответ отрецательный попросить пользователя ввести значение.

[Official DOC](https://getcomposer.org/apidoc/master/Composer/IO/IOInterface.html)

Краткий туториал

Что бы вывести сообщение используйте ф-цию `write`

```php
$io->write("some text");
```

Что бы вывести сообщение об ошибке используйте ф-цию `write`

```php
$io->writeError(("some text");
```

Что бы задать пользователю вопрос используйте ф-цию `write`

```php
$answer = $io->ask("question");
```
Для более детального изучения читайте [документацию](https://getcomposer.org/apidoc/master/Composer/IO/IOInterface.html).

[Пример использования IO](https://github.com/zendframework/zend-expressive-skeleton/blob/fb1c4bb037ba56f15eff07a3e5f2dd4d81e9e02a/src/ExpressiveInstaller/OptionalPackages.php#L264)

## Debug

Для убдобства отладки ваших Installers есть [скрипт `InstallerSelfCall`, ознакомится с ним можно тут](./InstallerSelfCall.md).