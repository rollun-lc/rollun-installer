# Тестирование

## Как писать тесты для инсталлеров
Простой пример тестов - `rollun\test\installer\Install\InstallerAbstractTest`.  
Если кратко:
Класс TestCase'а наследуем от rollun\installer\TestCase\InstallerTestCase. Для создания тестируемоого класса инсталлера потребуются $container и $io. Код:  

        $container = $this->getContainer();

        $userInput = "y\n";
        $outputStream = $this->getOutputStream();
        $io = $this->getIo($userInput, $outputStream);

        $installer = new MyInstaller($container, $io);

$userInput - это то, что пользователь вводит в консоль в ответ на вопросы инсталлера.

$outputStream можно не создавать, если не нужно тестировать вывод инсталлера в консоль. Вот так:

     $io = $this->getIo($userInput);

Тест работы инсталлера:

        $resalt = $installer->install();

        //$resalt содержит конфиг
        $this->assertEquals( ['param'=>'value'], $resalt);

        rewind($outputStream); //НЕ ЗАБЫВАЙТЕ ЭТО СДЕЛАТЬ!
        $this->assertEquals("Do you want to installl it?", stream_get_contents($outputStream));


## Запуск тестов

Перед запуском тестов выполните composer lib install для того что бы запустить инсталлеры.

Что бы запустить тесты вы обязаны поставить переменую окружения `APP_ENV` в `dev`.
После этого переменую `APP_ENV` можно будет переопределить через аргумент командной строки при запуске unit тестов
либо с помощью заголовка `APP_ENV` в http запросе.

Пример тестов которые тестируют разное окружение -- [skeleton-test](https://github.com/rollun-com/rollun-skeleton/tree/master/tests/src/Api)

## Отладка

Если вам нужно отладить инcталлеры вы можете [воспользоваться скриптом ](https://github.com/rollun-com/rollun-installer/blob/master/docs/InstallerSelfCall.md)
Он позволит запускать конкретный инталлер без запуска composer.
 
