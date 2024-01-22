<?php

namespace Shasoft\SqlQueryBuilder\Command\Trait;

use Shasoft\SqlQueryBuilder\Column;

// Функция для получения колонки [именованного] контекста
trait TraitColumn
{
    // Получить колонку
    public function column(string $columnName, ?string $contextName = null): Column
    {
        // Определить контекст
        $context = $this->context->context($contextName);
        // Создать колонку
        return new Column(
            $context->tableAlias,
            $context->table->column($columnName)
        );
    }
}
