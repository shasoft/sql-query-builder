<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\SqlQueryBuilder\Column;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitJoin;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitName;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitColumn;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitRelation;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitSelectJoinItemShare;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillTheJoinMustBeMadePrimaryKeyColumns;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillFilteringConditionsAreNotAvailableInJoins;

// СоединениЕ
class JoinItem
{
    // Функции соединений
    use TraitJoin;
    // Общие функции для Select|JoinItem
    use TraitSelectJoinItemShare;
    // Функция для сохранения именованного контекста
    use TraitName;
    // Функция для получения колонки [именованного] контекста
    use TraitColumn;
    // Соединение через отношение
    use TraitRelation;
    // Поля связи
    protected array $on;
    // Конструктор
    public function __construct(
        protected ContextCommand $context,
        protected string $type,
        array $on,
        array $selectJoinColumns
    ) {
        // Добавить колонкИ для выбора
        $this->context->root()->addSelectColumns(
            $this->context->tableAlias,
            $this->context->table,
            $selectJoinColumns
        );
        // Поля связи
        $this->on = [];
        foreach ($on as $columnNameJoin => $columnNameSelect) {
            if ($columnNameSelect instanceof Column) {
                $columnSelect = $columnNameSelect;
            } else {
                $columnSelect = new Column(
                    $this->context->parent->tableAlias,
                    $this->context->parent->table->column($columnNameSelect)
                );
            }
            $this->on[$columnNameJoin] = $columnSelect;
        }
        //s_dd($on, $this->on);
    }
    // Добавить условия фильтрации
    public function where(\Closure $cb): static
    {
        // Создать фильтрацию
        $where = new Where($this->context);
        // Вызвать функцию
        $cb($where);
        // Условия верхнего уровня
        $rootWhere = $this->context->root()->where();
        // Добавить условие в список условий
        DbSchemaReflection::getObjectMethod(
            $rootWhere,
            'pushCondition'
        )->invoke(
            $rootWhere,
            $where
        );
        // Вернуть указатель на себя
        return $this;
    }
    // Получить текст SQL запроса
    protected function sql(): string
    {
        $ret = '';
        // Команда + имя таблицы
        $ret = $this->type . ' JOIN ' . $this->context->contextQuery->quote($this->context->table->tabname());
        // Псевдоним
        $ret .= ' ' . $this->context->contextQuery->quote($this->context->tableAlias);
        // Поля связи
        $ret .= ' ON ';
        $on = [];
        foreach ($this->on as $columnNameJoin => $columnNameSelect) {
            $on[] =
                $this->context->contextQuery->sqlColumn($this->context->tableAlias, $this->context->table->column($columnNameJoin)) .
                ' = ' .
                $this->context->contextQuery->sqlColumn($columnNameSelect->tableAlias(), $columnNameSelect->column());
        }
        $ret .= implode(' AND ', $on);
        // Вернуть результат
        return $ret;
    }
}
