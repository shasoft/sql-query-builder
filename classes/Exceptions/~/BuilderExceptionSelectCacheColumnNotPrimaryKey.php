<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionFillColumnNotPrimaryKey extends BuilderException
{
    // Конструктор
    public function __construct(string $tableClass, string $name)
    {
        parent::__construct("В режиме КЭШирования нельзя использовать колонку `{$name}` таблицы `{$tableClass}` так как она не входит в первичный ключ");
    }
}
