<?php

namespace Shasoft\SqlQueryBuilder;

use Shasoft\SqlQueryBuilder\Column;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\State\StateColumn;
use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\SqlQueryBuilder\ContextQuery;
use Shasoft\SqlQueryBuilder\Command\Where;
use Shasoft\SqlQueryBuilder\Command\Select;
use Shasoft\SqlQueryBuilder\Command\JoinItem;

// Контекст команды
class ContextCommand
{
    // Список колонок для выбора
    public array $selectColumns = [];
    // Список колонок для фильтрации
    public array $whereColumns = [];
    // Список соединений
    public array $joinItems = [];
    // Условия фильтрации
    public ?Where $where = null;
    // Псевдоним таблицы
    public string $tableAlias;
    // Команды
    public array $commands = [];
    // Команда SELECT
    public ?Select $select = null;
    // Конструктор
    public function __construct(
        public ?ContextQuery $contextQuery,
        public ?self $parent,
        public ?StateTable $table,

    ) {
        // Сгенерировать имя псевдонима для таблицы
        $this->tableAlias = $this->contextQuery->generateTableAlias($table);
    }
    // Получить контекст верхнего уровня
    public function root(): static
    {
        $ret = $this;
        while (!is_null($ret->parent)) {
            $ret = $ret->parent;
        }
        return $ret;
    }
    // Получить объект фильтрации
    public function where(): Where
    {
        if (is_null($this->where)) {
            $this->where = new Where($this);
        }
        return $this->where;
    }
    // Получить контекст
    public function context(?string $contextName): static
    {
        return is_null($contextName) ?
            $this :
            $this->contextQuery->shareContexts[$contextName];
    }
    // Получить колонку контекста
    public function column(string|Column $columnName): Column
    {
        if ($columnName instanceof Column) {
            return $columnName;
        }
        return new Column($this->tableAlias, $this->table->column($columnName));
    }
    // Сортировка
    public function orderBy(Column $column, bool $up = true): static
    {
        //
        if (empty($this->commands['orderBy'])) {
            $this->commands['orderBy'] = [];
        }
        // Добавить сортировку
        $this->commands['orderBy'][] = $this->contextQuery->sqlColumn(
            $column->tableAlias(),
            $column->column()
        ) . ' ' . ($up ? 'ASC' : 'DESC');
        // Вернуть указатель на себя
        return $this;
    }
    // Группировка
    public function groupBy(Column $column): static
    {
        //
        if (empty($this->commands['groupBy'])) {
            $this->commands['groupBy'] = [];
        }
        // Добавить группировку
        $this->commands['groupBy'][] = $this->contextQuery->sqlColumn(
            $column->tableAlias(),
            $column->column()
        );
        // Вернуть указатель на себя
        return $this;
    }
    // Фильтрация групп
    public function having(
        ContextCommand $context,
        string $columnName,
        string $op,
        mixed $value
    ): static {
        // Разобрать имя колонки на имя функции и имя колонки
        $columnItem = $this->splitColumnName($columnName);
        // Проверим наличие указанной колонки
        $column = $context->table->column($columnItem['name']);
        //
        if (empty($this->commands['having'])) {
            $this->commands['having'] = [];
        }
        //
        $sqlColumn = $this->contextQuery->sqlColumn($context->tableAlias, $column);
        if (!empty($columnItem['func'])) {
            $sqlColumn = strtoupper($columnItem['func']) . '(' . $sqlColumn . ')';
        }
        // Добавить фильтрацию групп
        $op = $this->contextQuery->op($op);
        if ($op == 'BETWEEN') {
            $this->commands['having'][] = $sqlColumn . ' BETWEEN ' . $column->input($value[0]) . ' AND ' . $column->input($value[1]);
        } else if ($op == 'NOT BETWEEN') {
            $this->commands['having'][] = $sqlColumn . ' NOT BETWEEN ' . $column->input($value[0]) . ' AND ' . $column->input($value[1]);
        } else {
            $this->commands['having'][] = $sqlColumn . $op . $column->input($value);
        }
        // Вернуть указатель на себя
        return $this;
    }
    // Разобрать имя колонки на имя функции и имя колонки
    public function splitColumnName(string $columnName): array
    {
        // А может есть функция?
        $funcName = null;
        $pos = strpos($columnName, '(');
        if ($pos !== false) {
            $funcName = trim(substr($columnName, 0, $pos));
            $columnName = trim(substr($columnName, $pos + 1));
            $pos = strrpos($columnName, ')');
            if ($pos !== false) {
                $columnName = trim(substr($columnName, 0, $pos));
            }
        }
        return [
            'func' => $funcName,
            'name' => $columnName
        ];
    }
    // Разобрать колонку на составляющие
    public function parseSelectColumns(string $tableAlias, StateTable $table, array $selectColumns): array
    {
        $ret = [];
        // Колонки
        if (empty($selectColumns)) {
            foreach ($table->columns() as $columnName => $column) {
                $ret[] = [
                    'tableAlias' => $tableAlias,
                    'column' => $column,
                    'alias' => null,
                    'func' => null,
                    'name' => $column->name()
                ];
            }
        } else {
            foreach ($selectColumns as $columnName => $columnAlias) {
                // Может псевдоним поля задан через `поле=>псевдоним`?
                if (is_integer($columnName)) {
                    $columnName = $columnAlias;
                    $columnAlias = null;
                }
                // Сохранить оригинальное значение
                $columnNameOrigin = $columnName;
                // Может псевдоним задан `поле as псевдоним`?
                $pos = stripos($columnName, ' as ');
                if ($pos !== false) {
                    $columnAlias = trim(substr($columnName, $pos + 4));
                    $columnName = trim(substr($columnName, 0, $pos));
                }
                // Разобрать имя колонки на имя функции и имя колонки
                $columnItem = $this->splitColumnName($columnName);
                // Если нет псевдонима, но указана функция
                if (empty($columnAlias) && !empty($columnItem['func'])) {
                    $columnAlias = $columnNameOrigin;
                }
                $ret[] = [
                    'tableAlias' => $tableAlias,
                    'column' => $table->column($columnItem['name']),
                    'alias' => $columnAlias,
                    'func' => $columnItem['func'],
                    'name' => is_null($columnAlias) ? $columnItem['name'] : $columnAlias
                ];
            }
        }
        return $ret;
    }
    // Добавить колонкИ для выбора
    public function addSelectColumns(string $tableAlias, StateTable $table, array $selectColumns): void
    {
        // Получить контекст команды
        $contextCommand = $this->root();
        // Добавить поля
        $contextCommand->selectColumns = array_merge(
            $contextCommand->selectColumns,
            $this->parseSelectColumns($tableAlias, $table, $selectColumns)
        );
    }
    // Добавить колонку для фильтрации
    public function addWhereColumn(string $tableAlias, StateColumn $column, mixed $value): string
    {
        // Добавить параметр запроса и получить его имя
        $paramName = $this->contextQuery->addSqlParam($column, $value);
        // Добавить в список фильтрации
        $this->whereColumns[] = [
            'tableAlias' => $tableAlias,
            'column' => $column
        ];
        return $paramName;
    }
    // Соединение
    public function joinItem(string $type, string $tableClass, array $on, array $selectJoinColumns): JoinItem
    {
        // Создать объект
        $joinItem = new JoinItem(
            new ContextCommand(
                $this->contextQuery,
                $this,
                $this->contextQuery->contextBuilder->stateDatabase->table($tableClass)
            ),
            $type,
            $on,
            $selectJoinColumns
        );
        // Добавить
        $this->root()->joinItems[] = $joinItem;
        // Вернуть новое созданное соединение
        return $joinItem;
    }
    // Получить SQL код списка полей
    public function sqlColumns(): string
    {
        return implode(', ', array_map(function (array $selectColumn) {
            $ret =
                $this->contextQuery->quote($selectColumn['tableAlias']) .
                '.' .
                $this->contextQuery->quote($selectColumn['column']->name());
            if (!empty($selectColumn['func'])) {
                $ret = strtoupper($selectColumn['func']) . '(' . $ret . ')';
            }
            if (!empty($selectColumn['alias'])) {
                $ret .= ' AS ' . $this->contextQuery->quote($selectColumn['alias']);
            }
            return $ret;
        }, $this->selectColumns));
    }
    // Получить SQL код фильтрации
    public function sqlWhere(): string
    {
        return
            is_null($this->where) ? '' :
            DbSchemaReflection::getObjectMethod($this->where, 'sql')
            ->invoke($this->where);
    }
    // Получить SQL код соединений
    public function sqlJoins(): string
    {
        $ret = '';
        foreach ($this->joinItems as $joinItem) {
            $ret .= ' ' . DbSchemaReflection::getObjectMethod($joinItem, 'sql')
                ->invoke($joinItem);
        }
        return $ret;
    }
    // Сгенерировать ключ строки таблицы
    public function getKeyRow(array $row, ?array $keys = null): string
    {
        if (is_null($keys)) {
            $keyValues = array_values($row);
        } else {
            $keyValues = [];
            foreach ($keys as $key) {
                $keyValues[] = $row[$key];
            }
        }
        return $this->table->getExtraData('prefixKey') . serialize($keyValues);
    }
    // Удалить элементы КЭШа
    public function deleteItemsCache(ContextCommand $context, string $whereSql, array $params): int
    {
        $ret = 0;
        // Определить имя первичного индекс
        $indexPrimaryColumns = $context->table->getExtraData('pk');
        if (!empty($indexPrimaryColumns)) {
            // Выбрать первичный ключ для каждой удаляемой записи
            $selectColumns = [];
            foreach (array_keys($indexPrimaryColumns) as $columnName) {
                $selectColumns[] =
                    $context->contextQuery->quote($context->tableAlias) .
                    '.' .
                    $context->contextQuery->quote($columnName);
            }
            $selectSql = 'SELECT ' . implode(',', $selectColumns) . ' FROM ' .
                $context->contextQuery->quote($context->table->tabname()) .
                ' ' .
                $context->contextQuery->quote($context->tableAlias);
            //
            if (!empty($whereSql)) {
                $selectSql .= ' WHERE ' . $whereSql;
            }
            //
            $this->contextQuery->params = $params;
            $pdoStatement = $this->contextQuery->runSql($selectSql);
            if ($pdoStatement) {
                $keys = [];
                $countKeys = 0;
                while ($row = $pdoStatement->fetch(\PDO::FETCH_NUM)) {
                    // Сгенерировать ключ строки
                    $keys[] = $this->getKeyRow($row);
                    $countKeys++;
                    // Если набралось много ключей
                    if ($countKeys > 1000) {
                        //s_dump('deleteItems', $keys);
                        $ret += $countKeys;
                        // то удалить значения по этим ключам
                        $this->contextQuery->contextBuilder->cache->deleteItems($keys);
                        $keys = [];
                        $countKeys = 0;
                    }
                }
                if (!empty($keys)) {
                    //s_dump('deleteItems', $keys);
                    // Удалить значения по ключам
                    $this->contextQuery->contextBuilder->cache->deleteItems($keys);
                    $ret += count($keys);
                }
            }
        }
        return $ret;
    }
}
