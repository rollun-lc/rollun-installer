#InstallerSelfCall
Скрипт для запуска отдельных Installer класов.

Пример использования.  
Первым параметром задаем имя класса, вторым не обазательным параметром задаем запускаемый метод.  
По умолчанию будет запущен метод `install()`.

Для Unix

```bash
    bash vendor/bin/InstallerSelfCall "rollun\datastore\DataStore\Eav\Installer"
```

```bash
    bash vendor/bin/InstallerSelfCall "rollun\datastore\DataStore\Eav\Installer" install
```

```bash
    bash vendor/bin/InstallerSelfCall "rollun\datastore\DataStore\Eav\Installer" uninstall
```

```bash
    bash vendor/bin/InstallerSelfCall "rollun\datastore\DataStore\Eav\Installer" reinstall
```

Или для Win

```bash
   vendor\bin\InstallerSelfCall.bat "rollun\datastore\DataStore\Eav\Installer"
```

```bash
   vendor\bin\InstallerSelfCall.bat "rollun\datastore\DataStore\Eav\Installer" install
```

```bash
   vendor\bin\InstallerSelfCall.bat "rollun\datastore\DataStore\Eav\Installer" uninstall
```

```bash
   vendor\bin\InstallerSelfCall.bat "rollun\datastore\DataStore\Eav\Installer" reinstall
```
