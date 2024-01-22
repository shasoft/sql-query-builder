<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionFillOnlyOneFilterConditionPerColumnIsAllowed extends BuilderException
{
    // Конструктор
    public function __construct(string $tableClass, string $name)
    {
        parent::__construct("В режиме КЭШирования допустимо только одно условие фильтрации по колонке `{$name}` таблицы `{$tableClass}`");
    }
}
