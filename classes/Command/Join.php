<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitJoin;
use Shasoft\SqlQueryBuilder\Command\Trait\TraitColumn;

// СоединениЯ
class Join
{
    // Функции соединений
    use TraitJoin;
    // Функция для получения колонки [именованного] контекста
    use TraitColumn;
    // Конструктор
    public function __construct(
        protected ContextCommand $context
    ) {
    }
}
