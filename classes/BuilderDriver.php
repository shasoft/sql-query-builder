<?php

namespace Shasoft\SqlQueryBuilder;

use Shasoft\DbSchema\DbSchemaDriver;
use Shasoft\SqlQueryBuilder\ContextCommand;

// Драйвер построителя запросов
abstract class BuilderDriver
{
    // Конструктор
    public function __construct(
        protected DbSchemaDriver $dbSchemaDriver
    ) {
    }
    // Получить SQL кода удаления
    abstract public function sqlDelete(ContextCommand $context, string $whereSql): string;
}
