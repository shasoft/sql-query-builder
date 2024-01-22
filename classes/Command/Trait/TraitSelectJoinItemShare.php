<?php

namespace Shasoft\SqlQueryBuilder\Command\Trait;

use Shasoft\SqlQueryBuilder\Column;

// Общие функции для Select|JoinItem
trait TraitSelectJoinItemShare
{
    // Сортировка
    public function orderBy(string|Column $columnName, bool $up = true): static
    {
        $this->context->root()->orderBy(
            $this->context->column($columnName),
            $up
        );
        // Вернуть указатель на себя
        return $this;
    }
    // Группировка
    public function groupBy(string|Column $columnName): static
    {
        $this->context->root()->groupBy(
            $this->context->column($columnName)
        );
        // Вернуть указатель на себя
        return $this;
    }
    // Фильтрация групп
    public function having(string $columnName, string $op, mixed $value, ?string $contextName = null): static
    {
        $this->context->root()->having(
            $this->context->context($contextName),
            $columnName,
            $op,
            $value
        );
        // Вернуть указатель на себя
        return $this;
    }
}
