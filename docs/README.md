## Документация rollun-installer

Библиотека install позволяет произвести настройку окружение для вашей библиотеки или приложения.   

Вы должны создать реализации интерфейса `InstallerInterface` в которых и будет описана процедура настройки окружения.
Данные реализации обязаны содержать в себе суфикс `Installer`.

Так же существует `InstallerAbstract` абстрактная реализация интерфейса. 
При ее использоваии вам нужно реализовать метды `install` и `uninstall`.

При запуске инсталяции, будут найдены все подобные инсталлеры в порядке **сверху вниз**.
> Учитывайте это при напсании своих инсталлеров если вам важен порядок их вызова. 

Данная библиотека позволяет настраивать окружение для зависимых библиотек, но не гарантирует порядок выполнения данной настройки.

Сначало будут обрабатыватся инсталлеры зависимых библиотек, а в конце ваши.

### Пример

Пример инсталлера который создает требуемый для приложения файл

```php
    
    namespace zaboy\logger;
    use Composer\IO\IOInterface;
    use Interop\Container\ContainerInterface;
    use zaboy\installer\Command;
    use zaboy\installer\Install\InstallerAbstract;
    class Installer extends InstallerAbstract
    {
        const LOGS_DIR = 'logs';
        const LOGS_FILE = 'logs.txt';
        
        /**
         * Clean all installation
         * @return void
         */
        public function uninstall()
        {
            if (constant('APP_ENV') !== 'dev') {
                $this->ioComposer->write("constant('APP_ENV') !== 'dev' It has did nothing");
                exit;
            }
            $publicDir = Command::getPublicDir();
            if (file_exists($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR . DIRECTORY_SEPARATOR . self::LOGS_FILE)) {
                unlink($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR . DIRECTORY_SEPARATOR . self::LOGS_FILE);
            }
            if (is_dir($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR)) {
                rmdir($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR);
            }
        }
        /**
         * install
         * @return void
         */
        public function install()
        {
            if (constant('APP_ENV') !== 'dev') {
                $this->ioComposer->write("constant('APP_ENV') !== 'dev' It has did nothing");
                exit;
            }
            $publicDir = Command::getPublicDir();
            mkdir($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR);
            fopen($publicDir . DIRECTORY_SEPARATOR . self::LOGS_DIR . DIRECTORY_SEPARATOR . self::LOGS_FILE, "w");
        }
    }
    
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
  
Для того что бы можно было запускать инсталлеры используя композер вы должны добавить следующий учаток кода в секцию 
`scripts` файла `composer.json`
 ```json
   {
       "scripts": {
              "lib": "rollun\\installer\\Command::command"
        }
   }
 ```
> Без обромляющих символов `{` и `}`. 
Теперь после того как все предыдущее шаги были сделаны, вы можете используя команды 
* `composer lib install` - Запускать инсталяторы для настрройки окружения. 
* `composer lib uninstall` - Удалять настроки окружения.

Так же 

## Composer\IO\IOInterface

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