<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\SqlQueryBuilder\Command\Join;
use Shasoft\SqlQueryBuilder\ContextQuery;
use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitName;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitColumn;

// Удаление данных
// https://metanit.com/sql/mysql/3.5.php
class Delete
{
    // Функция для сохранения именованного контекста
    use TraitName;
    // Функция для получения колонки [именованного] контекста
    use TraitColumn;
    // Контекст
    protected ContextCommand $context;
    // Конструктор
    public function __construct(
        ContextQuery $contextQuery,
        string $tableClass
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
    }
    // Добавить условия фильтрации
    public function where(\Closure $cb): static
    {
        // Вызвать пользовательскую функцию
        $cb($this->context->where());
        // Вернуть указатель на себя
        return $this;
    }
    /*
    PostgreSQL не поддерживает соединения в DELETE
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
    // Выполнить удаление
    public function exec(): int
    {
        // Условия фильтрации
        $whereSql = $this->context->sqlWhere();
        // Сохранить параметры
        $params = $this->context->contextQuery->params;
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
        // Получить SQL кода удаления
        $sql = $this->context->contextQuery->contextBuilder->driver->sqlDelete($this->context, $whereSql);
        // Выполнить SQL запрос и вернуть количество удаленных записей
        return $this->context->contextQuery->runSql($sql)->rowCount();
    }
}
