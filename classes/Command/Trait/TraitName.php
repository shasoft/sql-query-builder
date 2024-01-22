<?php

namespace Shasoft\SqlQueryBuilder\Command\Trait;

// Функция для сохранения именованного контекста
trait TraitName
{
    // Сохранить наименование
    public function name(string $contextName): static
    {
        // Общие контексты
        $this->context->contextQuery->shareContexts[$contextName] = $this->context;
        // Вернуть указатель на себя
        return $this;
    }
}
