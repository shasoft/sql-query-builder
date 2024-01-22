<?php

namespace Shasoft\SqlQueryBuilder;

use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\DbSchemaExtraData;
use Shasoft\DbSchema\DbSchemaMigrations;
use Shasoft\DbSchema\Index\IndexPrimary;


// Расширенные данные
class BuilderExtraData
{
    // Выполнить дополнительный расчет
    static public function run(DbSchemaMigrations $migrations): void
    {
        $chars = [];
        for ($index = 0; $index < 260; $index++) {
            $ch = chr($index);
            if (ctype_print($ch)) {
                $chars[$ch] = 1;
            }
        }
        $chars = array_keys($chars);
        $index = 0;
        $prefixKeys = [];
        foreach ($migrations->database()->tables() as $table) {
            $prefixKey = '';
            $indexTable = $index;
            while (true) {
                if ($indexTable < count($chars)) {
                    $prefixKey .= $chars[$indexTable];
                    break;
                } else {
                    $prefixKey .= $chars[$indexTable % count($chars)];
                    $indexTable = intval($indexTable / count($chars));
                }
            }
            //
            $prefixKeys[$table->name()] = $prefixKey;
            //
            $index++;
        }
        //
        $migrations->extraData(
            // Установить для таблицы список колонок первичного ключа
            function (StateTable $table) use ($prefixKeys) {
                // Определить имя первичного индекса
                $pk = [];
                foreach ($table->indexes() as $index) {
                    if ($index->value(Type::class) == IndexPrimary::class) {
                        // Определить список колонок первичного индекса
                        $pk = array_flip($index->columns());
                        break;
                    }
                }
                DbSchemaExtraData::set($table, 'pk', $pk);
                //DbSchemaExtraData::set($table, 'prefixKey', $prefixKeys[$table->name()]);
                DbSchemaExtraData::set($table, 'prefixKey', strtoupper($table->name()));
                // Проставить флаг Первичный ключ для каждой колонки таблицы
                foreach ($table->columns() as $columnName => $column) {
                    DbSchemaExtraData::set(
                        $column,
                        'pk',
                        array_key_exists($columnName, $pk)
                    );
                }
            }
        );
    }
}
