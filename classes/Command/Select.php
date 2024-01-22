<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\SqlQueryBuilder\Paginator;
use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\SqlQueryBuilder\Command\Join;
use Shasoft\SqlQueryBuilder\ContextQuery;
use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitName;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitColumn;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitRelation;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitSelectJoinItemShare;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillItIsNecessaryToSpecifyTheFilteringConditions;

// Выборка данных
// https://metanit.com/sql/mysql/3.2.php
class Select
{
    // Общие функции для Select|JoinItem
    use TraitSelectJoinItemShare;
    // Функция для сохранения именованного контекста
    use TraitName;
    // Функция для получения колонки [именованного] контекста
    use TraitColumn;
    // Соединение через отношение
    use TraitRelation;
    // Контекст
    protected ContextCommand $context;
    // Команды
    protected array $commands = [];
    // Конструктор
    public function __construct(
        protected ContextQuery $contextQuery,
        string $tableClass,
        array $selectColumns,
    ) {
        // Создать контекст
        $this->context = new ContextCommand(
            // Контекст запроса
            $contextQuery,
            // Родительский контекст
            null,
            // Таблица
            $contextQuery->contextBuilder->stateDatabase->table($tableClass)
        );
        // Добавить колонкИ для выбора
        $this->context->addSelectColumns(
            $this->context->tableAlias,
            $this->context->table,
            $selectColumns
        );
    }
    // Добавить условия фильтрации
    public function where(\Closure $cb): static
    {
        // Вызвать пользовательскую функцию
        $cb($this->context->where());
        // Вернуть указатель на себя
        return $this;
    }
    // Добавить соединения
    public function join(\Closure $cb): static
    {
        $cb(new Join($this->context));
        // Вернуть указатель на себя
        return $this;
    }
    // Лимитирование
    public function limit(int $count, int $offset = 0): static
    {
        $this->commands['limit'] = $count;
        // Смещение
        return $this->offset($offset);
    }
    // Смещение
    protected function offset(int $offset): static
    {
        if ($offset == 0) {
            if (array_key_exists('offset', $this->commands)) {
                unset($this->commands['offset']);
            }
        } else {
            $this->commands['offset'] = $offset;
        }
        // Вернуть указатель на себя
        return $this;
    }
    // Выбрать только уникальные значения
    public function Distinct(): static
    {
        $this->commands['Distinct'] = 1;
        // Вернуть указатель на себя
        return $this;
    }
    // Получить текст SQL запроса
    protected function sql(bool $hasAllCount = false): string
    {
        // Сгенерировать SQL запрос
        $ret = 'SELECT ';
        // Выборка уникальных значений
        if (array_key_exists('Distinct', $this->commands)) {
            $ret .= 'DISTINCT';
        }
        // Список полей
        if ($hasAllCount) {
            $ret .= 'COUNT(*) AS ' . $this->contextQuery->quote('cnt');
        } else {
            $ret .= $this->context->sqlColumns();
        }
        // Таблица
        $ret .= ' FROM ' .
            $this->contextQuery->quote($this->context->table->tabname()) .
            ' ' .
            $this->contextQuery->quote($this->context->tableAlias);
        // Получить SQL код соединений
        $ret .=  $this->context->sqlJoins();
        // Условия
        $whereSql = $this->context->sqlWhere();
        if (!empty($whereSql)) {
            $ret .= ' WHERE ' . $whereSql;
        }
        // Группировка
        if (!empty($this->context->commands['groupBy'])) {
            $ret .= ' GROUP BY ' . implode(', ', $this->context->commands['groupBy']);
        }
        // Фильтрация групп
        if (!empty($this->context->commands['having'])) {
            $ret .= ' HAVING ' . implode(', ', $this->context->commands['having']);
        }
        // Сортировка
        if (!empty($this->context->commands['orderBy'])) {
            $ret .= ' ORDER BY ' . implode(', ', $this->context->commands['orderBy']);
        }
        // Лимит
        if (!$hasAllCount) {
            if (array_key_exists('limit', $this->commands)) {
                $ret .= ' LIMIT ' . $this->commands['limit'];
            }
            if (array_key_exists('offset', $this->commands)) {
                $ret .= ' OFFSET ' . $this->commands['offset'];
            }
        }
        return $ret;
    }
    // Получить количество строк, попадающих под выборку
    public function count(): int
    {
        // Получить SQL запрос
        $sql = $this->sql(true);
        // Выполнить SQL запрос и Получить данные
        $row = $this->contextQuery->runSql($sql)->fetch(\PDO::FETCH_ASSOC);
        // Вернуть полученное значение
        return $row['cnt'];
    }
    // Получить строки с данными
    public function get(): array
    {
        // Получить SQL запрос
        $sql = $this->sql(false);
        // Выполнить SQL запрос и Получить данные
        $rows = $this->contextQuery->runSql($sql)->fetchAll(\PDO::FETCH_ASSOC);
        // Сформировать конвертеры
        $conversions = [];
        foreach ($this->context->selectColumns as $selectColumn) {
            $column = $selectColumn['column'];
            $name = empty($selectColumn['alias']) ? $column->name() :  $selectColumn['alias'];
            $conversions[$name] = $column;
        }
        //s_dump($conversions, $rows, $this->context->selectColumns, $rows, $this);
        // Конвертировать результат
        foreach ($rows as &$row) {
            foreach ($row as $name => &$value) {
                $value = $conversions[$name]->output($value);
            }
        }
        // Вернуть результат
        return $rows;
    }
    // Получить одну строку
    public function first(): array|false
    {
        if (array_key_exists('limit', $this->commands)) {
            $this->limit(1, $this->commands['limit'][0]);
        } else {
            $this->limit(1);
        }
        // Получить SQL запрос
        $sql = $this->sql(false);
        // Выполнить SQL запрос и Получить данные
        return $this->contextQuery->runSql($sql)->fetch(\PDO::FETCH_ASSOC);
    }
    // Получить ответ в виде пагинации
    public function pagination(int $perPage, int $page = 0): Paginator
    {
        // Определить общее количество строк попадающих под условие
        $maxItems = $this->count();
        // Установить лимит
        $this->limit($perPage, $page * $perPage);
        // Получить данные
        $rows = $this->get();
        // Вернуть объект постраничной навигации
        return new Paginator($page, $perPage, $maxItems, $rows);
    }
}
