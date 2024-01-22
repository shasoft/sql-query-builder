<?php

namespace Shasoft\SqlQueryBuilder;

// Постраничная навигация
class Paginator
{
    // Конструктор
    public function __construct(protected $page, protected $perPage, protected $maxItems, protected $items)
    {
    }
    // Текущая страница
    public function page()
    {
        return $this->page;
    }
    // Количество элементов на странице
    public function perPage()
    {
        return $this->perPage;
    }
    // Начальный элемент
    public function from()
    {
        return $this->page;
    }
    // Конечный элемент
    public function to()
    {
        return $this->page + $this->perPage - 1;
    }
    // Конечная страница
    public function endPage()
    {
        if ($this->maxItems > 0) {
            return intval(floor(($this->maxItems - 1) / $this->perPage));
        }
        return 0;
    }
    // Элементы
    public function items()
    {
        return $this->items;
    }
    // Получить параметры
    public function args(?array $items = null)
    {
        return [
            'page' => $this->page(),
            'perPage' => $this->perPage(),
            'from' => $this->from(),
            'to' => $this->to(),
            'endPage' => $this->endPage(),
            'items' => is_null($items) ? $this->items() : $items,
        ];
    }
}
