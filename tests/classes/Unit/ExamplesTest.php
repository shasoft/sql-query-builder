<?php

namespace Shasoft\SqlQueryBuilder\Tests\Unit;


use PHPUnit\Framework\TestCase;
use Shasoft\PsrCache\CacheItemPool;
use Psr\Cache\CacheItemPoolInterface;
use Shasoft\SqlQueryBuilder\Tests\ExampleBase;
use Shasoft\SqlQueryBuilder\Tests\ExampleFill;
use Shasoft\SqlQueryBuilder\Tests\ExampleMain;
use Shasoft\PsrCache\Adapter\CacheAdapterArray;

class ExamplesTest extends TestCase
{
    // Выбор по полям
    static protected function invokeTest(string $classname, string $name, ?CacheItemPoolInterface $cache = null): void
    {
        // Создать построитель запросов
        $builder = ExampleBase::createBuilder($name, true, $cache);
        // Создать пример
        $example = new $classname([$name]);
        // Выполнить все примеры
        foreach ($example->get() as $title => $exampleFn) {
            $rc = $example->runExample($builder, $exampleFn, $title);
            self::assertFalse($rc['err'], $title . ': ' . $rc['message']);
        }
    }
    public function testExampleMainMySql()
    {
        $this->invokeTest(ExampleMain::class, 'mysql');
    }
    public function testExampleMainPostgreSql()
    {
        $this->invokeTest(ExampleMain::class, 'pgsql');
    }
    public function testExampleFillMySql()
    {
        $this->invokeTest(ExampleFill::class, 'mysql', new CacheItemPool(new CacheAdapterArray()));
    }
    public function testExampleFillPostgreSql()
    {
        $this->invokeTest(ExampleFill::class, 'pgsql', new CacheItemPool(new CacheAdapterArray()));
    }
}
