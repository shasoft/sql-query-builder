<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\SqlQueryBuilder\ContextQuery;
use Shasoft\SqlQueryBuilder\Command\Values;
use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitName;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitColumn;

// Контейнер значений
abstract class ContainerValues
{
    // Функция для сохранения именованного контекста
    use TraitName;
    // Функция для получения колонки [именованного] контекста
    use TraitColumn;
    // Контекст
    protected ContextCommand $context;
    // Значения
    protected array $values = [];
    // Конструктор
    public function __construct(
        ContextQuery $contextQuery,
        string $tableClass
    ) {
        // Создать контекст
        $this->context = new ContextCommand(
            // Контекст запроса
            $contextQuery,
            // Родительский контекст
            null,
            // Таблица
            $contextQuery->contextBuilder->stateDatabase->table($tableClass)
        );
    }
    // Указать значение поля колонки
    public function value(string $columnName, mixed $value): static
    {
        $this->values[$columnName] = $value;
        // Вернуть указатель на себя
        return $this;
    }
    // Вставить значения
    public function values(array|\Closure $values): static
    {
        if (is_callable($values)) {
            $values(new Values($this, $this->context));
        } else {
            foreach ($values as $columnName => $value) {
                $this->value($columnName, $value);
            }
        }
        // Вернуть указатель на себя
        return $this;
    }
}
