<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionFillCommandNotSupport extends BuilderException
{
    // Конструктор
    public function __construct(string $command)
    {
        parent::__construct("В режиме КЭШирования команда `{$command}` не поддерживается");
    }
}
