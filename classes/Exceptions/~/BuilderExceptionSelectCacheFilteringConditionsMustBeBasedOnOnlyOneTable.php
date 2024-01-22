<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionFillFilteringConditionsMustBeBasedOnOnlyOneTable extends BuilderException
{
    // Конструктор
    public function __construct(array $tableClasses)
    {
        parent::__construct("В режиме КЭШирования условия фильтрации должны быть только по одной таблице " .
            implode(
                ',',
                array_map(function (string $tableClass) {
                    return '`' . $tableClass . '`';
                }, $tableClasses)
            ));
    }
}
