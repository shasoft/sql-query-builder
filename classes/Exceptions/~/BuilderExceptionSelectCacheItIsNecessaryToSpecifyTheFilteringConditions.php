<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionFillItIsNecessaryToSpecifyTheFilteringConditions extends BuilderException
{
    // Конструктор
    public function __construct()
    {
        parent::__construct("В режиме КЭШирования необходимо обязательно указать условия фильтрации");
    }
}
