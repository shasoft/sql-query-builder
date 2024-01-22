<?php

namespace Shasoft\SqlQueryBuilder\Command;

use Shasoft\SqlQueryBuilder\Builder;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\SqlQueryBuilder\Command\Join;
use Shasoft\SqlQueryBuilder\ContextQuery;
use Shasoft\SqlQueryBuilder\Command\Where;
use Shasoft\SqlQueryBuilder\ContextCommand;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionNotImplemented;

// Заполнение
class Fill
{
    // Временный префикс
    protected string $tempPrefix;
    // Режим КЭШа включен?
    protected bool $enableCache = true;
    // Конструктор
    public function __construct(protected array &$rows, protected Builder $builder)
    {
        $this->tempPrefix = '~~~';
    }
    // Установить режим работы с КЭШем
    public function cacheEnable(bool $value): static
    {
        $this->enableCache = $value;
        // Вернуть указатель на себя
        return $this;
    }
    // Отключить КЭШ
    public function cacheOff(): static
    {
        $this->enableCache = false;
        // Вернуть указатель на себя
        return $this;
    }
    // Включить КЭШ
    public function cacheOn(): static
    {
        $this->enableCache = true;
        // Вернуть указатель на себя
        return $this;
    }
    // Добавить данные из таблицы
    public function fromTable(string $tableClass, array $pkColumns, array $selectColumns = [], ?\Closure $cb = null): static
    {
        //--
        $contextQuery = new ContextQuery(
            DbSchemaReflection::getObjectPropertyValue($this->builder, 'contextBuilder', null),
            false
        );
        //--
        $contextCommand = new ContextCommand(
            $contextQuery,
            null,
            $contextQuery->contextBuilder->stateDatabase->table($tableClass)
        );
        // Создать объект
        $from = new From($contextCommand, $pkColumns, $selectColumns);
        if (is_callable($cb)) {
            $cb($from);
        }
        // Если КЭШ выключен
        if ($this->enableCache == false || is_null($contextQuery->contextBuilder->cache)) {
            // Выполнить заполнение через Select
            $this->runContextSelect($contextCommand);
        } else {
            // Выполнить заполнение через Cache
            $this->runContextCache($contextCommand);
        }
        // Вернуть указатель на себя
        return $this;
    }
    // Получить ключ строки
    protected function getKey(array $row, array $rowKeys): string
    {
        $keyValue = [];
        foreach ($rowKeys as $rowKey) {
            $keyValue[] = $row[$rowKey];
        }
        return serialize($keyValue);
    }
    // Увеличить уровень всех родительских выборов
    protected function levelUp(array &$selectGroups, string $groupName, int $level): void
    {
        $selectGroups[$groupName]['level'] = max($level, $selectGroups[$groupName]['level']);
        $groupItem = $selectGroups[$groupName];
        if (array_key_exists('parents', $groupItem)) {
            foreach ($groupItem['parents'] as $parentTableAlias) {
                $this->levelUp($selectGroups, $parentTableAlias, $level + 1);
            }
        }
    }
    // Выполнить заполнение через Cache
    protected function runContextCache(ContextCommand $context): void
    {
        $rootAliasBindColumns = [];
        foreach ($context->joinItems[$context->tableAlias] as $columnName) {
            $rootAliasBindColumns[$columnName] = $this->tempPrefix;
        }
        unset($context->joinItems[$context->tableAlias]);
        $selects = array_map(function (array $bindColumns) {
            //
            $parents = [];
            foreach ($bindColumns as $bindColumn) {
                $parents[$bindColumn->tableAlias()] = 1;
            }
            //
            $bind = [];
            foreach ($bindColumns as $columnName => $column) {
                $bind[$column->column()->name()] = $column->tableAlias();
            }
            //
            return [
                'root' => false,
                'bind' => $bind,
                'parents' => array_keys($parents),
            ];
        }, $context->joinItems);
        //
        $selects[$context->tableAlias] = [
            'root' => true,
            'bind' => $rootAliasBindColumns,
            'parents' => []
        ];
        // Разобьем все выборки по группам
        $selectGroups = [];
        foreach ($selects as $tableAlias => $select) {
            //$select['name'] = $tableAlias;
            $table = $context->contextQuery->aliases[$tableAlias];
            $name = $table->name();
            if (array_key_exists($name, $selectGroups)) {
                // Если таблица ссылается на саму себя, то её в отдельную группу
                // Первую выборку всегда в отдельную группу!
                $hasRefToThis = $select['root'] ? true : false;
                if (!$hasRefToThis) {
                    foreach ($select['parents'] as $parentAliasName) {
                        $tableParent = $context->contextQuery->aliases[$parentAliasName];
                        if (spl_object_id($tableParent) == spl_object_id($table)) {
                            $hasRefToThis = true;
                            break;
                        }
                    }
                }
                if ($hasRefToThis) {
                    // Выделить в отдельную группу
                    $name .= '~' . $tableAlias;
                    $selectGroups[$name] = [
                        'table' => $table,
                        'binds' => [$tableAlias => $select['bind']],
                        'parents' => []
                    ];
                } else {
                    $selectGroups[$name]['binds'][$tableAlias] = $select['bind'];
                }
            } else {
                $selectGroups[$name] = [
                    'table' => $table,
                    'binds' => [$tableAlias => $select['bind']],
                    'parents' => []
                ];
            }
            // Добавить родительские выборки
            foreach ($select['parents'] as $parentAliasName) {
                $selectGroups[$name]['parents'][$parentAliasName] = 1;
            }
            $selects[$tableAlias]['group'] = $name;
        }
        // Заменить ссылки на псевдонимы таблиц на имена групп
        $selectGroups = array_map(function (array $selectGroup) use ($selects) {
            $parents = [];
            foreach ($selectGroup['parents'] as $parentAliasName => $_) {
                $parents[] = $selects[$parentAliasName]['group'];
            }
            $selectGroup['parents'] = $parents;
            $selectGroup['children'] = [];
            $selectGroup['level'] = 0;
            return $selectGroup;
        }, $selectGroups);
        // Проставим количество дочерних групп
        foreach ($selectGroups as $groupName => $selectGroup) {
            if (array_key_exists('parents', $selectGroup)) {
                foreach ($selectGroup['parents'] as $parentGroupName) {
                    $selectGroups[$parentGroupName]['children'][$groupName] = 1;
                }
            }
        }
        //
        $selectGroups = array_map(function (array $selectGroup) {
            $selectGroup['children'] = array_keys($selectGroup['children']);
            //$selectGroup['tabname'] = $selectGroup['table']->tabname();
            return $selectGroup;
        }, $selectGroups);
        //
        foreach ($selectGroups as $tableAlias => $selectGroup) {
            if (count($selectGroup['children']) == 0) {
                $this->levelUp($selectGroups, $tableAlias, 1);
            }
        }
        //
        $selectGroups = array_values($selectGroups);
        usort($selectGroups, function (array $group1, array $group2) {
            return $group1['level'] < $group2['level'] ? 1 : ($group1['level'] > $group2['level'] ? -1 : 0);
        });
        //s_dd($selects, $selectGroups);
        // Сформировать список строк
        $rows = [];
        foreach ($this->rows as $row) {
            $rows[] = [$this->tempPrefix => $row];
        }
        //s_dump($rows, $selectGroups);
        // Проведем все выборки
        foreach ($selectGroups as $selectGroup) {
            // Таблица 
            $table = $selectGroup['table'];
            // Префикс ключа
            $prefixKey = $table->getExtraData('prefixKey');
            // Выберем список уникальных значений
            $values = [];
            $rows = array_map(function (array $row) use ($selectGroup, &$values) {
                foreach ($selectGroup['binds'] as $tableAlias => $bind) {
                    $hasNoSkip = true;
                    // Сгенерировать ключ строки
                    $keyValue = [];
                    foreach ($bind as $columnName => $aliasName) {
                        $rowAlias = $row[$aliasName];
                        if ($rowAlias) {
                            $keyValue[] = $rowAlias[$columnName];
                        } else {
                            //$keyValue[] = null;
                            $hasNoSkip = false;
                            break;
                        }
                    }
                    if ($hasNoSkip) {
                        $keyRow = serialize($keyValue);
                        // И записать его в соответствующее поле
                        $row[$tableAlias] = $keyRow;
                        // Записать в значение
                        $values[$keyRow] = 1;
                    } else {
                        // И записать его в соответствующее поле
                        $row[$tableAlias] = null;
                    }
                }
                // Вернуть измененную строку
                return $row;
            }, $rows);
            // Сгенерировать список ключей КЭШа
            $keys = array_map(function (string $keyRow) use ($prefixKey) {
                return $prefixKey . $keyRow;
            }, array_keys($values));
            // Проверить наличие значений в КЭШе
            $cacheItems = [];
            foreach ($context->contextQuery->contextBuilder->cache->getItems($keys) as $cacheItem) {
                $cacheItems[$cacheItem->getKey()] = $cacheItem;
            }
            // Убрать из списка значений те значения, что прочитали из КЭШа
            $values = array_keys($values);
            $values = array_filter($values, function (string $keyRow) use ($cacheItems, $prefixKey) {
                $cacheItem = $cacheItems[$prefixKey . $keyRow];
                return !$cacheItem->isHit();
            });
            // Поля первичного ключа
            $pkColumns = array_keys($table->getExtraData('pk'));
            // Если есть данные для выборки
            if (!empty($values)) {
                // то выбрать данные из БД
                //$values = array_map('unserialize', $values);
                $values = array_map(function (string $str) {
                    $data = unserialize($str);
                    return $data[0];
                }, $values);
                // Ключевое поле
                $pkColumn = $table->column($pkColumns[0]);
                // Сгенерировать SQL
                $context->contextQuery->params = [];
                $context->contextQuery->paramTypes = [];
                $context->whereColumns = [];
                $inValues = [];
                foreach ($values as $value) {
                    $inValues[] = $context->addWhereColumn('', $pkColumn, $value);
                }
                $sql =
                    'SELECT * FROM ' . $context->contextQuery->quote($table->tabname()) . ' WHERE '
                    . $context->contextQuery->quote($pkColumns[0]);
                if (count($inValues) == 1) {
                    $sql .= ' = ' . $inValues[0];
                } else {
                    $sql .= ' in '
                        . '(' . implode(',', $inValues) . ')';
                }
                //
                $sqlRc = $context->contextQuery->runSql($sql);
                while (($row = $sqlRc->fetch(\PDO::FETCH_ASSOC)) !== false) {
                    // Сгенерировать ключ строки
                    $keyValue = [];
                    foreach ($pkColumns as $columnName) {
                        $keyValue[] = $row[$columnName];
                    }
                    $keyRow = serialize($keyValue);
                    // Конвертировать
                    foreach ($row as $columnName => $value) {
                        $row[$columnName] = $table->column($columnName)->output($value);
                    }
                    //
                    $cacheItem = $cacheItems[$prefixKey . $keyRow] ?? null;
                    if (!is_null($cacheItem)) {
                        $cacheItem->set($row);
                        // Сохранить
                        $context->contextQuery->contextBuilder->cache->saveDeferred($cacheItem);
                    }
                }
            }
            // Проставить значения
            $rows = array_map(function (array $row) use ($selectGroup, $prefixKey, $cacheItems) {
                foreach ($selectGroup['binds'] as $tableAlias => $bind) {
                    // И записать его в соответствующее поле
                    $key = $prefixKey . $row[$tableAlias];
                    $row[$tableAlias] = [];
                    if (array_key_exists($key, $cacheItems)) {
                        $row[$tableAlias] = $cacheItems[$key]->get();
                    }
                }
                //
                return $row;
            }, $rows);
        }
        // Сохранить все изменения в КЭШ
        $context->contextQuery->contextBuilder->cache->commit();
        // Сформировать список полей для копирования в исходный массив
        $copyColumns = [];
        foreach ($context->contextQuery->aliases as $tableAlias => $table) {
            $columns = [];
            foreach ($context->parseSelectColumns(
                $tableAlias,
                $table,
                $context->selectColumns[$tableAlias]
            ) as $columnItem) {
                $columns[$columnItem['column']->name()] = $columnItem['name'];
            }
            $copyColumns[$tableAlias] = $columns;
        }
        // Копировать
        foreach ($this->rows as $index => &$rowTo) {
            $rowFrom = $rows[$index];
            foreach ($copyColumns as $tableAlias => $columns) {
                foreach ($columns as $from => $to) {
                    $rowFromAlias = $rowFrom[$tableAlias];
                    if ($rowFromAlias) {
                        $rowTo[$to] = $rowFromAlias[$from];
                    } else {
                        $rowTo[$to] = null;
                    }
                }
            }
        }
    }
    // Выполнить заполнение через Select
    protected function runContextSelect(ContextCommand $context): void
    {
        // Поля для удаления
        $fieldnameForUnset = [];
        // Данные будем выбирать через Select+Join
        $select = $this->builder->select(
            $context->table->name(),
            $context->selectColumns[$context->tableAlias]
        );
        // Контекст ВЫБОРА
        $contextSelect = DbSchemaReflection::getObjectPropertyValue(
            $select,
            'context',
            null
        );
        // Определить поля первичного индекса и их соответствие в выбираемых полях
        $pkColumns = $context->table->getExtraData('pk');
        foreach ($pkColumns as $tabName => $_) {
            // Ищем 
            $hasNoFind = true;
            foreach ($contextSelect->selectColumns as $selectColumn) {
                if ($selectColumn['column']->name() == $tabName) {
                    $pkColumns[$tabName] = $tabName;
                    $hasNoFind = false;
                    break;
                }
            }
            // Если не нашли
            if ($hasNoFind) {
                // Имя колонки в строке
                $rowColumnName = $this->tempPrefix . $tabName;
                // Добавить в список для выборки
                $contextSelect->addSelectColumns(
                    $contextSelect->tableAlias,
                    $contextSelect->table,
                    [$tabName => $rowColumnName]
                );
                // Установить соответствие: `поле таблицы` <=> `поле строки`
                $pkColumns[$tabName] = $rowColumnName;
                // Добавить в список для удаления
                $fieldnameForUnset[$rowColumnName] = 1;
            }
        }
        //s_dd($contextSelect, $pkColumns);
        // Убрать из списка
        unset($context->selectColumns[$context->tableAlias]);
        // Добавить в запросе остальные команды
        $select->name($context->tableAlias)->join(function (Join $join) use ($context) {
            foreach ($context->selectColumns as $aliasName => $selectColumns) {
                // Трансформировать поля связи
                $on = [];
                foreach ($context->joinItems[$aliasName] as $columnName => $column) {
                    $on[$columnName]  = $join->column(
                        $column->column()->name(),
                        $column->tableAlias()
                    );
                }
                // Создать соединение
                $join
                    ->left(
                        $context->contextQuery->aliases[$aliasName]->name(),
                        $on,
                        $selectColumns
                    )
                    ->name($aliasName);
            }
        });
        // Колонка с ключом в строке
        $rowKeyColumn = $this->tempPrefix . 'key';
        // Сгенерировать ключ для каждой строки
        $rowKeys = $context->joinItems[$context->tableAlias];
        $values = [];
        $this->rows = array_map(
            function (array $row) use ($rowKeyColumn, $rowKeys, &$values) {
                // Получить ключ строки
                $key = $this->getKey($row, $rowKeys);
                $values[$key] = 1;
                $row[$rowKeyColumn] = $key;
                return $row;
            },
            $this->rows
        );
        // Добавить условия
        if (count($rowKeys) == 1) {
            $select->where(function (Where $where) use ($values, $pkColumns) {
                //
                $values = array_map(function (string $str) {
                    $data = unserialize($str);
                    return $data[0];
                }, array_keys($values));
                //
                $where->inArray(
                    array_keys($pkColumns)[0],
                    $values
                );
            });
        } else {
            // Нет реализации!
            throw new BuilderExceptionNotImplemented;
        }
        // Список полей для копирования
        $copyColumns = [];
        foreach ($contextSelect->selectColumns as $item) {
            $name = $item['name'];
            if (!array_key_exists($name, $fieldnameForUnset)) {
                $copyColumns[] = $item['name'];
            }
        }
        // Добавить 
        $selectRows = $select->get();
        // Разобрать по ключам
        $rowByKey = [];
        foreach ($selectRows as $row) {
            $rowByKey[$this->getKey($row, $pkColumns)] = $row;
        }
        $this->rows = array_map(
            function (array $row) use ($rowKeyColumn, $rowByKey, $copyColumns) {
                // Получить ключ строки
                $key = $row[$rowKeyColumn];
                // Удалить ключ
                unset($row[$rowKeyColumn]);
                // Выбрать строку значений БД по ключу
                $rowSelect = $rowByKey[$key] ?? [];
                // Копировать
                foreach ($copyColumns as $columnName) {
                    $row[$columnName] = $rowSelect[$columnName] ?? null;
                }
                // Вернуть измененную строку
                return $row;
            },
            $this->rows
        );
    }
}
