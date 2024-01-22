<?php
// https://mydev.fun/laravel/query-builder
use Shasoft\Filesystem\File;
use Shasoft\PsrCache\CacheItemPool;
use Shasoft\SqlQueryBuilder\Tests\Example;
use Shasoft\SqlQueryBuilder\Tests\ExampleFill;
use Shasoft\SqlQueryBuilder\Tests\ExampleMain;
use Shasoft\PsrCache\Adapter\CacheAdapterArray;
use Shasoft\SqlQueryBuilder\Tests\ExampleCache;

require_once __DIR__ . '/../classes/bootstrap.php';

s_dump_run(function () {
    // Создать пример
    $classname = ExampleFill::class;
    //$classname = ExampleMain::class;
    $example = new $classname(['mysql'], new CacheItemPool(new CacheAdapterArray()));
    // Установить короткий режим имени таблицы
    $example->setShortTableName(array_key_exists('short', $_GET));
    // Сохранить
    if (array_key_exists('save', $_GET)) {
        // 
        File::save(
            __DIR__ . '/../../docs/examples.md',
            (new ExampleMain(
                ['mysql', 'pgsql'],
                new CacheItemPool(new CacheAdapterArray())
            ))->markdown('Sql Query Builder examples')
        );
        // 
        File::save(
            __DIR__ . '/../../docs/examples-fill.md',
            (new ExampleFill(
                ['mysql'],
                new CacheItemPool(new CacheAdapterArray())
            ))->markdown('Sql Query Builder fill examples')
        );
    }
    // Выполнить тесты и вывести
    echo $example->html();
});
