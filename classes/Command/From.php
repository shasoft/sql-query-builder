<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\SqlQueryBuilder\Column;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitName;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitColumn;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillTheJoinMustBeMadePrimaryKeyColumns;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin;

// ДоВыбрать данные
class From
{
    // Функция для сохранения именованного контекста
    use TraitName;
    // Функция для получения колонки [именованного] контекста
    use TraitColumn;
    // Конструктор
    public function __construct(
        protected ContextCommand $context,
        array $bind,
        array $selectColumns
    ) {
        // Контекст верхнего уровня
        $rootContext = $context->root();
        // Добавить поля для выборки
        $rootContext->selectColumns[$context->tableAlias] = $selectColumns;
        // Список ключевых полей
        $primaryColumns = $context->table->getExtraData('pk');
        //s_dd($context);
        if (is_null($context->parent)) {
            $rootContext->joinItems[$context->tableAlias] = $bind;
            //
            if (count($bind) != count($primaryColumns)) {
                // В соединении участвуют не все поля первичного индекса
                throw new BuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin(
                    $context->table->name(),
                    $bind
                );
            }
        } else {
            // Поля связи
            $bindTransform = [];
            foreach ($bind as $columnNameJoin => $columnNameSelect) {
                if ($columnNameSelect instanceof Column) {
                    $columnSelect = $columnNameSelect;
                } else {
                    $columnSelect = new Column(
                        $context->parent->tableAlias,
                        $context->parent->table->column($columnNameSelect)
                    );
                }
                $bindTransform[$columnNameJoin] = $columnSelect;
            }
            $rootContext->joinItems[$context->tableAlias] = $bindTransform;
            // Соединение должно выполняться по всем ключевым поля таблицы
            $hasOk = true;
            foreach ($bindTransform as $columnName => $_) {
                // Поле входит в первичный ключ?
                $hasOk = array_key_exists($columnName, $primaryColumns);
                if ($hasOk) {
                    // Удалить обработанное поле из списка
                    unset($primaryColumns[$columnName]);
                } else {
                    // Соединение должно выполняться по ключевым полям
                    throw new BuilderExceptionFillTheJoinMustBeMadePrimaryKeyColumns(
                        $context->table->name(),
                        $columnName
                    );
                }
            }
            if (!empty($primaryColumns)) {
                // В соединении участвуют не все поля первичного индекса
                throw new BuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin(
                    $context->table->name(),
                    array_keys($primaryColumns)
                );
            }
        }
    }
    // Добавить данные из таблицы
    public function joinTable(string $tableClass, array $bind, array $selectColumns = [], ?\Closure $cb = null): From
    {
        //--
        $contextCommand = new ContextCommand(
            $this->context->contextQuery,
            $this->context,
            $this->context->contextQuery->contextBuilder->stateDatabase->table($tableClass)
        );
        // Создать объект
        $from = new From($contextCommand, $bind, $selectColumns);
        if (is_callable($cb)) {
            $cb($from);
        }
        // Вернуть указатель на себя
        return $from;
    }
    // Добавить данные из отношения
    public function joinRelation(string $name, array $selectColumns = [], ?\Closure $cb = null): From
    {
        $stateRelation = $this->context->table->relation($name);
        $bind = [];
        foreach ($stateRelation->to()->columns() as $from => $to) {
            $bind[$from] = $this->context->column($to);
        }
        //--
        $contextCommand = new ContextCommand(
            $this->context->contextQuery,
            $this->context,
            $stateRelation->to()->table()
        );
        // Создать объект
        $from = new From($contextCommand, $bind, $selectColumns);
        if (is_callable($cb)) {
            $cb($from);
        }
        //s_dd($name, $stateRelation, $bind,  $from);
        // Вернуть указатель на себя
        return $from;
    }
}
