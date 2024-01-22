<?php

namespace Shasoft\SqlQueryBuilder\Command\Trait;

use Shasoft\SqlQueryBuilder\Command\JoinItem;

// СоединениЯ
trait TraitJoin
{
    // Соединение INNER
    public function inner(string $tableClass, array $on, array $selectColumns = []): JoinItem
    {
        return  $this->context->joinItem('INNER', $tableClass, $on, $selectColumns);
    }
    // Соединение LEFT
    public function left(string $tableClass, array $on, array $selectColumns = []): JoinItem
    {
        return  $this->context->joinItem('LEFT', $tableClass, $on, $selectColumns);
    }
    // Соединение LEFT
    public function right(string $tableClass, array $on, array $selectColumns = []): JoinItem
    {
        return  $this->context->joinItem('RIGHT', $tableClass, $on, $selectColumns);
    }
    /*
    В MySql FULL соединение не поддерживается
    Для совместимости вообще уберем этот вид соединения!
    // Соединение FULL
    public function full(string $tableClass, array $on, array $selectColumns = []): JoinItem
    {
        return  $this->context->joinItem('FULL', $tableClass, $on, $selectColumns);
    }
    //*/
}
