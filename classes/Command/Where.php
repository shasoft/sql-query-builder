<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\SqlQueryBuilder\Column;
use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitColumn;

// Фильтрация запроса
class Where
{
    // Функция для получения колонки [именованного] контекста
    use TraitColumn;
    // Тип операции для группы
    protected string $op;
    // Условия
    protected array $conditions = [];
    // Конструктор
    public function __construct(
        protected ContextCommand $context
    ) {
        // Тип группы по умолчанию
        $this->and();
    }
    // Тип группы = AND
    public function and(): static
    {
        $this->op = 'AND';
        // Вернуть указатель на себя
        return $this;
    }
    // Тип группы = OR
    public function or(): static
    {
        $this->op = 'OR';
        // Вернуть указатель на себя
        return $this;
    }
    // Условная составная часть запроса
    public function when(mixed $value, \Closure $ifCb, ?\Closure $elseCb = null)
    {
        if ($value) {
            $ifCb($this, $value);
        } elseif (is_callable($elseCb)) {
            $elseCb($this, $value);
        }
        // Вернуть указатель на себя
        return $this;
    }
    // Добавить условие 
    protected function addCondition(
        string $sqlValue,
        string|Column $columnName
    ): static {
        // Колонка контекста
        $columnContext = $this->context->column($columnName);
        // Получить информацию о колонке
        $column = $columnContext->column();
        // Получить SQL код поля
        $sql = $this->context->contextQuery->sqlColumn(
            $columnContext->tableAlias(),
            $column
        );
        // Добавить операцию
        $sql .= ' ' . $sqlValue;
        // Добавить условие в список условий
        $this->conditions[] = $sql;
        // Вернуть указатель на себя
        return $this;
    }
    // Добавить условия фильтрации
    protected function pushCondition(Where $where): void
    {
        // Добавить условие в список условий
        $this->conditions[] = $where->sql();
    }
    // Добавить условие
    public function cond(
        string|Column $columnName,
        string $op,
        mixed $value
    ): static {
        $op = $this->context->contextQuery->op($op);
        if ($op == 'BETWEEN') {
            $this->between($columnName, $value[0], $value[1]);
        } else if ($op == 'NOT BETWEEN') {
            $this->notBetween($columnName, $value[0], $value[1]);
        } else {
            // Колонка контекста
            $columnContext = $this->context->column($columnName);
            // Получить информацию о колонке
            $column = $columnContext->column();
            // Контекст
            $contextRoot = $this->context->root();
            // Добавить колонку в список фильтраций
            if ($value instanceof Column) {
                $sqlValue = $this->context->contextQuery->sqlColumn(
                    $value->tableAlias(),
                    $value->column()
                );
            } else {
                $sqlValue = $contextRoot->addWhereColumn(
                    $columnContext->tableAlias(),
                    $column,
                    $column->output($value)
                );
            }
            // Получить SQL код поля
            $sql = $this->context->contextQuery->sqlColumn(
                $columnContext->tableAlias(),
                $column
            );
            // Добавить операцию
            $sql .= ' ' . $op;
            // Добавить значение
            $sql .= ' ' . $sqlValue;
            // Добавить условие в список условий
            $this->conditions[] = $sql;
        }
        // Вернуть указатель на себя
        return $this;
    }
    // Добавить условие
    public function isNull(
        string|Column $columnName
    ): static {
        return $this->addCondition('IS NULL', $columnName);
    }
    // Добавить условие
    public function isNotNull(
        string|Column $columnName
    ): static {
        return $this->addCondition('IS NOT NULL', $columnName);
    }
    // Добавить условие
    public function like(
        string|Column $columnName,
        string $value
    ): static {
        return $this->cond($columnName, 'LIKE', $value);
    }
    // Добавить условие
    public function notLike(
        string|Column $columnName,
        string $value
    ): static {
        return $this->cond($columnName, 'NOT LIKE', $value);
    }
    // Добавить условие [NOT] BETWEEN
    protected function _between(
        string $op,
        string|Column $columnName,
        mixed $minValue,
        mixed $maxValue
    ): static {
        // Колонка контекста
        $columnContext = $this->context->column($columnName);
        // Получить информацию о колонке
        $column = $columnContext->column();
        // Добавить колонку в список фильтраций
        if ($minValue instanceof Column) {
            $sqlMinValue = $this->context->contextQuery->sqlColumn(
                $minValue->tableAlias(),
                $minValue->column()
            );
        } else {
            $sqlMinValue = $this->context->root()->addWhereColumn(
                $columnContext->tableAlias(),
                $column,
                $column->output($minValue)
            );
        }
        // Добавить колонку в список фильтраций
        if ($maxValue instanceof Column) {
            $sqlMaxValue = $this->context->contextQuery->sqlColumn(
                $maxValue->tableAlias(),
                $maxValue->column()
            );
        } else {
            $sqlMaxValue = $this->context->root()->addWhereColumn(
                $columnContext->tableAlias(),
                $column,
                $column->output($maxValue)
            );
        }
        // Получить SQL код поля
        $sql = $this->context->contextQuery->sqlColumn(
            $columnContext->tableAlias(),
            $column
        );
        // Добавить операцию
        $sql .= ' ' . $op;
        // Добавить значение
        $sql .= ' ' . $sqlMinValue . ' AND ' . $sqlMaxValue;
        // Добавить условие в список условий
        $this->conditions[] = $sql;
        // Вернуть указатель на себя
        return $this;
    }
    // Добавить условие BETWEEN
    public function between(
        string|Column $columnName,
        mixed $minValue,
        mixed $maxValue
    ): static {
        return $this->_between('BETWEEN', $columnName, $minValue, $maxValue);
    }
    // Добавить условие NOT BETWEEN
    public function notBetween(
        string|Column $columnName,
        mixed $minValue,
        mixed $maxValue
    ): static {
        return $this->_between('BETWEEN', $columnName, $minValue, $maxValue);
    }
    // Добавить условия фильтрации
    public function where(\Closure $cb): static
    {
        // Создать фильтрацию
        $where = new Where($this->context);
        // Вызвать функцию
        $cb($where);
        // Добавить условие в список условий
        $this->pushCondition($where);
        // Вернуть указатель на себя
        return $this;
    }
    // Добавить условие IN по массиву значений
    public function inArray(string|Column $columnName, array $values): static
    {
        // Нужно добавлять условие?
        if (!empty($values)) {
            if (count($values) == 1) {
                $this->cond($columnName, '=', $values[0]);
            } else {
                // Псевдоним таблицы
                $tableAlias = $this->context->tableAlias;
                // Контекст
                $contextRoot = $this->context->root();
                // Получить информацию о колонке
                $column = $contextRoot->table->column($columnName);
                // Конвертировать значения
                $sqlValues = [];
                // Добавить колонку в список фильтраций
                foreach ($values as $value) {
                    $sqlValues[] = $contextRoot->addWhereColumn(
                        $tableAlias,
                        $column,
                        $column->output($value)
                    );
                }
                // Получить SQL код поля
                $sql = $this->context->contextQuery->sqlColumn($this->context->tableAlias, $column);
                // Добавить операцию
                $sql .= ' IN';
                // Добавить значения
                $sql .= ' (' . implode(',', $sqlValues) . ')';
                // Добавить условие в список условий
                $this->conditions[] = $sql;
            }
        }
        // Вернуть указатель на себя
        return $this;
    }
    // Добавить условие IN по запросу SELECT
    public function inSelect(string|array $columnName, string $tableClass, string|array $selectColumnName, ?\Closure $cb = null): static
    {
        //
        if (is_array($columnName)) {
            $columnNames = $columnName;
        } else {
            $columnNames = [$columnName];
        }
        //
        $columns = [];
        foreach ($columnNames as $columnName) {
            // Получить информацию о колонке
            $column = $this->context->table->column($columnName);
            // Получить SQL код поля
            $columns[] = $this->context->contextQuery->sqlColumn($this->context->tableAlias, $column);
        }
        // Получить SQL код поля
        $sql = implode(', ', $columns);
        if (count($columns) > 1) {
            $sql = '(' . $sql . ')';
        }
        // Создать вложенный запрос
        $select = new Select(
            $this->context->contextQuery,
            $tableClass,
            is_array($selectColumnName) ? $selectColumnName : [$selectColumnName]
        );
        // Вызвать функцию обработки (если она есть)
        if (!is_null($cb)) {
            $cb($select);
        }
        // Добавить операцию
        $sql .= ' IN ';
        // Добавить значения
        $sql .=  '(' . DbSchemaReflection::getObjectMethod($select, 'sql')
            ->invoke($select, false) . ')';
        // Добавить условие в список условий
        $this->conditions[] = $sql;
        // Вернуть указатель на себя
        return $this;
    }
    // Получить текст SQL запроса
    protected function sql(): string
    {
        $ret =  implode(' ' . $this->op . ' ', $this->conditions);
        // Если условий больше 1
        if (count($this->conditions) > 1) {
            // то добавить скобки
            $ret = '(' . $ret . ')';
        }
        return $ret;
    }
}
