<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\SqlQueryBuilder\Command\Select;
use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\Command\ContainerValues;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitColumn;


// Значения команды ContainerValues
class Values
{
    // Функция для получения колонки [именованного] контекста
    use TraitColumn;
    // Конструктор
    public function __construct(
        protected ContainerValues $containerValues,
        protected ContextCommand $context
    ) {
    }
    // Указать значение поля
    public function value(string $columnName, mixed $value): static
    {
        $this->containerValues->value($columnName, $value);
        // Вернуть указатель на себя
        return $this;
    }
    // Указать значение поля из запроса Select
    public function valueSelect(string $columnNameInto, string $tableClass, string $columnNameFrom, ?\Closure $cb = null): static
    {
        // Создать вложенный запрос
        $select = new Select(
            $this->context->contextQuery,
            $tableClass,
            [$columnNameFrom]
        );
        // Вызвать функцию обработки (если она есть)
        if (!is_null($cb)) {
            $cb($select);
        }
        // Установить значение
        $this->containerValues->value($columnNameInto, $select);
        // Вернуть указатель на себя
        return $this;
    }
}
