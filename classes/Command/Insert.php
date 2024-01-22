<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\SqlQueryBuilder\Command\Select;
use Shasoft\SqlQueryBuilder\Command\ContainerValues;

// Добавление данных
// https://metanit.com/sql/mysql/3.1.php
class Insert extends ContainerValues
{
    // Выполнить добавление
    public function exec(): int|false
    {
        if (!empty($this->values)) {
            $columns = []; // Список полей
            $values = [];  // Значения
            foreach ($this->values as $columnName => $columnValue) {
                // Проверить наличие поля
                $column = $this->context->table->column($columnName);
                // Поле
                $columns[] = $this->context->contextQuery->quote($columnName);
                // Сформировать значение в формате SQL
                if ($columnValue instanceof Select) {
                    $sqlValue = '(' . DbSchemaReflection::getObjectMethod($columnValue, 'sql')
                        ->invoke($columnValue) . ')';
                } else {
                    $sqlValue =  $this->context->contextQuery->addSqlParam($column, $columnValue);
                }
                // Значение
                $values[] = $sqlValue;
            }
            // Сформировать SQL запрос
            $sql =
                'INSERT INTO ' .
                $this->context->contextQuery->quote($this->context->table->tabname()) .
                ' (' . implode(',', $columns) .
                ') VALUES (' .
                implode(',', $values) .
                ')';
            // Выполнить SQL запрос и вернуть идентификатор вставленной записи
            if ($this->context->contextQuery->runSql($sql)) {
                return $this->context->contextQuery->contextBuilder->pdo->lastInsertId();
            }
        }
        return false;
    }
}
