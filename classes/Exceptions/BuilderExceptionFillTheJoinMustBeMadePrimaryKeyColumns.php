<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionFillTheJoinMustBeMadePrimaryKeyColumns extends BuilderException
{
    // Конструктор
    public function __construct(string $tableClass, string $name)
    {
        parent::__construct("В режиме КЭШирования связывание с `{$tableClass}` должно быть выполнено по столбцам первичного ключа `{$name}`");
    }
}
