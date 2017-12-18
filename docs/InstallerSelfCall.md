#InstallerSelfCall
Скрипт для запуска отдельных Installer класов.

Пример использования.  
Первым параметром задаем имя класса, вторым не обазательным параметром задаем запускаемый метод.  
По умолчанию будет запущен метод `install()`.

Для Unix

```bash
    bash vendor/bin/InstallerSelfCall.php "rollun\datastore\DataStore\Eav\Installer"
```

```bash
    bash vendor/bin/InstallerSelfCall.php "rollun\datastore\DataStore\Eav\Installer" install
```

```bash
    bash vendor/bin/InstallerSelfCall.php "rollun\datastore\DataStore\Eav\Installer" uninstall
```

```bash
    bash vendor/bin/InstallerSelfCall.php "rollun\datastore\DataStore\Eav\Installer" reinstall
```

Или для Win

```bash
   vendor\bin\InstallerSelfCall.php.bat "rollun\datastore\DataStore\Eav\Installer"
```

```bash
   vendor\bin\InstallerSelfCall.php.bat "rollun\datastore\DataStore\Eav\Installer" install
```

```bash
   vendor\bin\InstallerSelfCall.php.bat "rollun\datastore\DataStore\Eav\Installer" uninstall
```

```bash
   vendor\bin\InstallerSelfCall.php.bat "rollun\datastore\DataStore\Eav\Installer" reinstall
```

Так же вы можете запускать используя **php**

```bash
    php vendor/rollun-com/rollun-installer/bin/InstallerSelfCall.php "rollun\datastore\DataStore\Eav\Installer" install   
```

Под **php** стоит воспринимать полный путь к исполняемой программе *php*.
Напримр используя *OpenServer* у вас будет путь примерно такой `D:\OpenServer\modules\php\PHP-7.1-x64\php.exe`