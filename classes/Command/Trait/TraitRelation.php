<?php

namespace Shasoft\SqlQueryBuilder\Command\Trait;

// Соединение через отношение
trait TraitRelation
{
    // Добавить соединение из отношения
    public function relation(string $name, array $selectColumns, \Closure $cb = null): static
    {
        $stateRelation = $this->context->table->relation($name);
        $on = [];
        foreach ($stateRelation->to()->columns() as $from => $to) {
            $on[$from] = $this->context->column($to);
        }
        //s_dd($stateRelation, $on);
        $joinItem = $this->context->joinItem(
            'LEFT',
            $stateRelation->to()->table()->name(),
            $on,
            $selectColumns
        );
        if (is_callable($cb)) {
            $cb($joinItem);
        }
        // Вернуть указатель на себя
        return $this;
    }
}
