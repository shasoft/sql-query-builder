<?php

namespace Shasoft\SqlQueryBuilder\Driver;

use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\BuilderDriver;

// Драйвер построителя запросов БД PostgreSql
class pgsql extends BuilderDriver
{
    // Получить SQL кода удаления
    public function sqlDelete(ContextCommand $context, string $whereSql): string
    {
        $ret = 'DELETE FROM ' .
            $this->dbSchemaDriver->quote($context->table->tabname()) .
            ' ' .
            $this->dbSchemaDriver->quote($context->tableAlias);
        // Условия
        if (!empty($whereSql)) {
            $ret .= ' WHERE ' . $whereSql;
        }
        return $ret;
    }
};
