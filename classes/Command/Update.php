<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\SqlQueryBuilder\Command\ContainerValues;

// Изменение данных
// https://metanit.com/sql/mysql/3.4.php
class Update extends ContainerValues
{
    // Добавить условия фильтрации
    public function where(\Closure $cb): static
    {
        // Вызвать пользовательскую функцию
        $cb($this->context->where());
        // Вернуть указатель на себя
        return $this;
    }
    /*
    PostgreSQL не поддерживает соединения в UPDATE
    Так пусть их вообще тут не будет
    Делайте всё через вложенные запросы!    
    // Добавить соединения
    public function join(\Closure $cb): static
    {
        $cb(new Join($this->context));
        // Вернуть указатель на себя
        return $this;
    }
    //*/
    // Выполнить изменение
    public function exec(): int
    {
        if (!empty($this->values)) {
            // Условия фильтрации
            $whereSql = $this->context->sqlWhere();
            // Сохранить параметры
            $params = $this->context->contextQuery->params;
            //
            $set = []; // Изменений
            foreach ($this->values as $columnName => $columnValue) {
                // Проверить наличие поля
                $column = $this->context->table->column($columnName);
                // Поле
                $sqlColumn = $this->context->contextQuery->quote($columnName);
                // Сформировать значение в формате SQL
                if ($columnValue instanceof Select) {
                    $sqlValue = '(' . DbSchemaReflection::getObjectMethod($columnValue, 'sql')
                        ->invoke($columnValue) . ')';
                } else {
                    $sqlValue =  $this->context->contextQuery->addSqlParam($column, $columnValue);
                }
                // Список изменений
                $set[] = $sqlColumn . '=' . $sqlValue;
            }
            // Сформировать SQL запрос
            $sql = 'UPDATE ' .
                $this->context->contextQuery->quote($this->context->table->tabname()) .
                ' ' .
                $this->context->contextQuery->quote($this->context->tableAlias);
            // Добавить SQL код соединений
            $sql .=  $this->context->sqlJoins();
            //
            $sql .= ' SET ' . implode(', ', $set);
            // Условия фильтрации
            if (!empty($whereSql)) {
                $sql .= ' WHERE ' . $whereSql;
            }
            // Выполнить SQL запрос и вернуть количество измененных записей
            $ret = $this->context->contextQuery->runSql($sql)->rowCount();
            // А может включен режим КЭШирования?
            $hasCache = !is_null($this->context->contextQuery->contextBuilder->cache);
            if ($hasCache) {
                // Удалить элементы КЭШа
                $this->context->deleteItemsCache(
                    $this->context,
                    $whereSql,
                    $params
                );
            }
            return $ret;
        }
        return false;
    }
}
