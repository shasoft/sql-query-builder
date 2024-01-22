<?php

namespace Shasoft\SqlQueryBuilder;

use Shasoft\DbTool\DbToolPdoLog;
use Shasoft\DbSchema\Command\PdoParam;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\State\StateColumn;
use Shasoft\SqlQueryBuilder\ContextBuilder;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionSqlError;

// Контекст запроса
class ContextQuery
{
    // Список используемых в запросе псевдонимов и соответствующих им таблиц
    public array $aliases = [];
    // Команды
    public array $commands = [];
    // Параметры
    public array $params = [];
    // Типы параметров
    public array $paramTypes = [];
    // Общие контексты
    public array $shareContexts = [];
    // Конструктор
    public function __construct(
        public ContextBuilder $contextBuilder
    ) {
    }
    // Обработать операцию
    public function op(string $op): string
    {
        $op = strtoupper(trim($op));
        $op = preg_replace('/\s+/', ' ', $op);
        return $op;
    }
    // Добавить параметр запроса и получить его имя
    public function addSqlParam(StateColumn $column, mixed $value): string
    {
        // Имя параметра
        $paramName = 'p' . count($this->params);
        if (is_null($value)) {
            // Конвертировать значение
            $this->params[$paramName] = null;
            // Определить тип параметра
            $this->paramTypes[$paramName] = \PDO::PARAM_NULL;
        } else {
            // Конвертировать значение
            $this->params[$paramName] = $column->input($value);
            // Определить тип параметра
            $this->paramTypes[$paramName] = $column->value(PdoParam::class, \PDO::PARAM_STR);
        }
        // Вернуть имя
        return ':' . $paramName;
    }
    // Разобрать имя колонки на имя функции и имя колонки
    public function splitColumnName(string $columnName): array
    {
        // А может есть функция?
        $funcName = null;
        $pos = strpos($columnName, '(');
        if ($pos !== false) {
            $funcName = trim(substr($columnName, 0, $pos));
            $columnName = trim(substr($columnName, $pos + 1));
            $pos = strrpos($columnName, ')');
            if ($pos !== false) {
                $columnName = trim(substr($columnName, 0, $pos));
            }
        }
        return [
            'func' => $funcName,
            'name' => $columnName
        ];
    }
    // Сгенерировать имя псевдонима для таблицы
    public function generateTableAlias(StateTable $table): string
    {
        $tmp = explode("\\", $table->name());
        if (true) {
            $ret = strtoupper(array_pop($tmp)) . '_';
            $index = 1;
            while (array_key_exists($ret . $index, $this->aliases)) {
                $index++;
            }
            $ret .= $index;
        } else {
            $ret = strtoupper(substr(array_pop($tmp), 0, 1));
            // Если в списке ЕСТЬ такое значение
            if (array_key_exists($ret, $this->aliases)) {
                for ($ich = ord('A'); $ich <= ord('Z'); $ich++) {
                    $ret = chr($ich);
                    if (!array_key_exists($ret, $this->aliases)) {
                        break;
                    }
                }
            }
        }
        // Записать
        $this->aliases[$ret] = $table;
        // Вернуть найденное имя
        return $ret;
    }
    // Заключить имя в кавычки
    public function quote(string $name): string
    {
        return $this->contextBuilder->dbSchemaDriver->quote($name);
    }
    // Получить SQL код поля
    public function sqlColumn(string $tableAlias, StateColumn $column): string
    {
        return $this->quote($tableAlias) . '.' . $this->quote($column->name());
    }
    // Выполнить SQL запрос
    public function runSql(string $sql): \PDOStatement|false
    {
        try {
            $query = $this->contextBuilder->pdo->prepare($sql);
            if ($query) {
                if (!empty($this->params)) {
                    // Привязать параметры
                    foreach ($this->params as $name => $value) {
                        $query->bindValue($name, $value, $this->paramTypes[$name]);
                    }
                }
                // Выполнить запрос
                if ($query->execute()) {
                    // Класс логирования доступен?
                    if (class_exists(DbToolPdoLog::class)) {
                        // Записать запрос в лог
                        DbToolPdoLog::write($query, $this->contextBuilder->pdo, $this->params);
                    }
                    //
                    return $query;
                }
            }
        } catch (\Throwable $t) {
            throw new BuilderExceptionSqlError(
                $sql,
                $this->params,
                $t->getMessage(),
                1,
                $t->getPrevious()
            );
        }
        //s_dump($sql, $this->args, $this->argTypes);
        return false;
    }
}
