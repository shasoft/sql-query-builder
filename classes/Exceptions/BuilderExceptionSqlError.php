<?php

namespace Shasoft\SqlQueryBuilder\Exceptions;

use Shasoft\SqlQueryBuilder\BuilderException;

// Исключение
class BuilderExceptionSqlError extends BuilderException
{
    // Конструктор
    public function __construct(
        protected string $sql,
        protected array $params,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    public function getSql(): string
    {
        return $this->sql;
    }
    public function getParams(): array
    {
        return $this->params;
    }
}
