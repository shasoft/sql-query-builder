<?php

namespace Shasoft\SqlQueryBuilder\Driver;

use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\BuilderDriver;

// Драйвер построителя запросов БД MySql
class mysql extends BuilderDriver
{
    // Получить SQL кода удаления
    public function sqlDelete(ContextCommand $context, string $whereSql): string
    {
        $sqlTableAlias = $this->dbSchemaDriver->quote($context->tableAlias);
        $ret = 'DELETE ' .
            $sqlTableAlias .
            ' FROM ' .
            $this->dbSchemaDriver->quote($context->table->tabname()) .
            ' ' .
            $sqlTableAlias;
        // Условия
        if (!empty($whereSql)) {
            $ret .= ' WHERE ' . $whereSql;
        }
        return $ret;
    }
};
