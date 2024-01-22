<?php

namespace Shasoft\SqlQueryBuilder;

use Shasoft\DbSchema\DbSchemaDriver;
use Psr\Cache\CacheItemPoolInterface;
use Shasoft\DbSchema\Command\DriverClass;
use Shasoft\DbSchema\State\StateDatabase;
use Shasoft\SqlQueryBuilder\Command\Fill;
use Shasoft\SqlQueryBuilder\Command\Load;
use Shasoft\SqlQueryBuilder\Command\Delete;
use Shasoft\SqlQueryBuilder\Command\Insert;
use Shasoft\SqlQueryBuilder\Command\Select;
use Shasoft\SqlQueryBuilder\Command\Update;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitFill;

// Выполнение SQL команд с помощью построителя запросов
// https://sql-academy.org/ru/guide
class Builder
{
    // Заполнение данных
    //use TraitFill;
    // Контекст построителя запросов
    protected ContextBuilder $contextBuilder;
    // Конструктор
    public function __construct(
        // PDO драйвер соединения с БД
        \PDO $pdo,
        // Состояние БД
        StateDatabase $stateDatabase,
        // Интерфейс КЕШирования
        ?CacheItemPoolInterface $cache = null
    ) {
        $this->contextBuilder = new ContextBuilder($this, $pdo, $stateDatabase, $cache);
    }
    // PDO соединение с БД
    public function pdo(): \PDO
    {
        return $this->contextBuilder->pdo;
    }
    // Состояние БД
    public function state(): StateDatabase
    {
        return $this->contextBuilder->stateDatabase;
    }
    // Создать контекст запроса
    protected function createQueryContext(): ContextQuery
    {
        return new ContextQuery($this->contextBuilder);
    }
    // Выборка данных
    public function select(string $tableClass, array $columns = []): Select
    {
        return new Select(
            $this->createQueryContext(),
            $tableClass,
            $columns
        );
    }
    // Заполнение данных
    public function fill(array &$rows): Fill
    {
        return new Fill($rows, $this);
    }
    // Добавление данных
    public function insert(string $tableClass): Insert
    {
        return new Insert(
            $this->createQueryContext(),
            $tableClass
        );
    }
    // Удаление данных
    public function delete(string $tableClass): Delete
    {
        return new Delete(
            $this->createQueryContext(),
            $tableClass
        );
    }
    // Изменение данных
    public function update(string $tableClass): Update
    {
        return new Update(
            $this->createQueryContext(),
            $tableClass
        );
    }
}
