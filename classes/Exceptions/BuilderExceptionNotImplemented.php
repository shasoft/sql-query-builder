<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionNotImplemented extends BuilderException
{
    // Конструктор
    public function __construct()
    {
        parent::__construct("Не реализовано");
    }
}
