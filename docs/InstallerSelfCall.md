#InstallerSelfCall
Скрипт для запуска отдельных Installer класов.

Пример использования.  
Первым параметром задаем имя класса, вторым не обазательным параметром задаем запускаемый метод.  
По умолчанию будет запущен метод `install()`.

```bash
    php bin/InstallerSelfCall.php "rollun\datastore\DataStore\Eav\Installer"
```

```bash
    php bin/InstallerSelfCall.php "rollun\datastore\DataStore\Eav\Installer" install
```

```bash
    php bin/InstallerSelfCall.php "rollun\datastore\DataStore\Eav\Installer" uninstall
```

```bash
    php bin/InstallerSelfCall.php "rollun\datastore\DataStore\Eav\Installer" reinstall
```

Для использования данного скрипта, скопируйте его в дерикторию **bin** свого проекта.