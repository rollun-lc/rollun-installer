# Zend Component Installer. Быстрый старт.

## Оформление пакета для Component Installer'а.

Это пакет, позволяющий автоматизировать установку и настройку пакетов. 
Обычно он уже имеется в зависимостях проекта Zend Expressive. Если же нет, то добавляем его:

    # Файл composer.json
    "require": {
        // ...
        "zendframework/zend-component-installer": "^1.0 || ^0.7.0",
        // ...
    }
    
У этого установщика компонентов есть требования к оформлению пакета/модуля:
1. Пакет должен быть оформлен по всем правилам оформления пакетов composer'а.
2. В файле composer.json пакета в секции "extra" необходимо указать следующее:

    # Файл composer.json
    "extra": {
        // ...
        "zf": {
            "component": "Component\NameSpace",
            "config-provider": "Component\NameSpace\ConfigProvider",
        }
        // ...
    }
    
Как видно из кода, мы указываем пространство имен компонента и название класса ConfigProvider'а. Конфигураторов в пакете
может быть несколько, тогда раздел "config-provider" становится массивом, в котором нужно указать через запятую все
классы конфигураторов.

Когда пакет оформлен таким образом, то после его установки запускается скрипт пост-инсталляции пакета zend-component-installer.
Он спросит, нужно ли добавлять ConfigProvider в конфигурацию проекта или нет. И добавит, если пользователь этого захочет.


## Автоматизация добавления маршрутов.

А как быть с маршрутами? Для этого Zend предлагает очень изящное решение. Для этого в пакете нужно создать специальный
делегатор, который будет содержать перечень маршрутов. А сам этот делегатор вешается на класс Zend\Expressive\Application.

    # Файл Component\NameSpace\Factory\RouteDelegator.php
    <?php
    
    namespace Component\NameSpace\Factory;
    
    use Interop\Container\ContainerInterface;
    use Component\NameSpace\Middleware\IndexAction;
    use Zend\Expressive\Application;
    
    class RouteDelegator
    {
        function __invoke(ContainerInterface $container, $serviceName, callable $callback)
        {
            /** @var $app Application */
            $app = $callback();
            // Устанавливаем маршруты:
            $app->get('/component', IndexAction::class, 'component-index'); // имя маршруту указываем любое, но стараемся, чтобы оно было уникальным
    
            return $app;
        }
    }
    
В файле ConfigProvider прописываем зависимость класса приложения от этого делегатора:

    # Файл Component\NameSpace\ConfigProvider.php
    <?php
    
    namespace Component\NameSpace;
    
    use Component\NameSpace\Factory\RouteDelegator;
    use Zend\Expressive\Application;
    
    class ConfigProvider
    {
        function __invoke()
        {
            return [
                'dependencies' => [
                    'delegators' => [
                        Application::class => [
                            RouteDelegator::class,
                        ],
                    ],
                ],
            ];
        }
    }
    
Это все. Когда ZendComponentInstaller установит пакет и пропишет его ConfigProvider в конфигурацию приложения, этот
делагатор будет вызываться всякий раз при создании класса приложения.


## Удаление пакета и его настроек из конфигурации приложения.

Для этого ничего особенного делать не нужно. Достаточно просто удалить зависимость приложения от установленного пакета 
(удалить пакет из секции "require" файла composer.json приложения) и запустить обновление composer'а. Инсталлер компонентов заметит,
что пакета нет и просто удалит его из конфигурации приложения.
 
> Тут есть одна проблема. Дело в том, что в одном из файлов ZendComponentInstaller имеется ошибка, которая не позволяет
ему завершить свою работу корректно. На эту тему уже создано [официальное обращение](https://github.com/zendframework/zend-component-installer/issues/39).
Но эта ошибка не мешает жить. Она появляется уже ПОСЛЕ того, как все изменения в конфигурацию проекта внесены. Поэтому,
достаточно просто еще раз запустить обновление composer'а.


## Что делать, если в приложении нет ZendComponentInstaller'а?

Как правило, такого не бывает, но теоретически возможно. В этом случае можно пойти двумя путями:

* указать зависимость приложения от этой библиотеки;
* или поставить эту зависимость прямо в пакет, который мы будем позже устанавливать.

Наличие инсталлера компонентов в каждом устанавливаемом пакете совершенно никак не мешает ни приложению, ни библиотеке/пакету,
ни самому инсталлеру. Второй вариант более универсальный, но он обязывает ставить дополнительную зависимость в пакет.


## Где и что можно об этом почитать?

* [Zend Component Installer](http://zendframework.github.io/zend-component-installer/)
* [Использование delegator для добавления роутинга в expressive](https://docs.zendframework.com/zend-expressive/cookbook/autowiring-routes-and-pipelines/#delegator-factories)