<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin extends BuilderException
{
    // Конструктор
    public function __construct(string $tableClass, array $columns)
    {
        parent::__construct(
            "В режиме КЭШирования при связывании с `{$tableClass}` участвуют не все колонки первичного индекса [`" . implode('`,`', $columns) . "`]"
        );
    }
}
