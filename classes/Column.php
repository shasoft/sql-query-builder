<?php

namespace Shasoft\SqlQueryBuilder;

use Shasoft\DbSchema\State\StateColumn;

// Колонка таблицы
class Column
{
    // Конструктор
    public function __construct(
        protected string $tableAlias,
        protected StateColumn $column,

    ) {
    }
    // Псевдоним таблицы
    public function tableAlias(): string
    {
        return $this->tableAlias;
    }
    // Колонка
    public function column(): StateColumn
    {
        return $this->column;
    }
}
