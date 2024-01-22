<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionFillFilteringConditionsAreNotAvailableInJoins extends BuilderException
{
    // Конструктор
    public function __construct(string $tableClassFrom, string $tableClassTo)
    {
        parent::__construct("В режиме КЭШирования условия фильтрации недоступны в соединениях (см. соединение с `{$tableClassFrom}`=>`{$tableClassTo}`)");
    }
}
