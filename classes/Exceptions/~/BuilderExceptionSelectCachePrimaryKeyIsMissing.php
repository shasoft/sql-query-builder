<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionFillPrimaryKeyIsMissing extends BuilderException
{
    // Конструктор
    public function __construct(string $tableClass)
    {
        parent::__construct("В режиме КЭШирования недопустимо использовать таблицу `{$tableClass}` так как у неё отсутствует первичный ключ");
    }
}
