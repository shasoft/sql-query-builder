<?php

namespace Shasoft\SqlQueryBuilder;

use Shasoft\DbSchema\DbSchemaDriver;
use Psr\Cache\CacheItemPoolInterface;
use Shasoft\DbSchema\Command\DriverClass;
use Shasoft\DbSchema\State\StateDatabase;
use Shasoft\SqlQueryBuilder\BuilderDriver;

// Контекст построителя запросов
class ContextBuilder
{
    // Драйвер миграций
    public DbSchemaDriver $dbSchemaDriver;
    // Драйвер построителя запросов
    public BuilderDriver $driver;
    // Конструктор
    public function __construct(
        public Builder $builder,
        // PDO драйвер соединения с БД
        public \PDO $pdo,
        // Состояние БД
        public StateDatabase $stateDatabase,
        // Интерфейс КЕШирования
        public ?CacheItemPoolInterface $cache
    ) {
        // Получить класс драйвера схемы БД
        $classname = $stateDatabase->value(DriverClass::class);
        // Создать объект драйвера схемы БД
        $this->dbSchemaDriver = new $classname;
        // Получить драйвер построителя запросов
        $classname = "Shasoft\\SqlQueryBuilder\\Driver\\" . $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        // Создать драйвер построителя запросов
        $this->driver = new $classname($this->dbSchemaDriver);
    }
}
