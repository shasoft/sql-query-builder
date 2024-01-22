<?php

namespace Shasoft\SqlQueryBuilder\Tests\Table;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchemaDev\Table\User;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchemaDev\Table\Article;
use Shasoft\DbSchema\Column\ColumnText;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\MinLength;
use Shasoft\DbSchema\Command\RelNameTo;
use Shasoft\DbSchema\Tests\Table\User0;
use Shasoft\DbSchema\Column\ColumnRefId;
use Shasoft\DbSchema\Command\RelTableTo;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Command\ReferenceTo;
use Shasoft\DbSchema\Reference\Reference;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Column\ColumnDatetime;
use Shasoft\DbSchema\Command\NumberOfSpaces;
use Shasoft\DbSchema\Relation\RelationManyToOne;
use Shasoft\DbSchema\Relation\RelationOneToMany;
use Shasoft\SqlQueryBuilder\MigrationCommand\CacheNone;

#[Title('Таблица без первичного ключа')]
class TabNoPrimaryKey
{
    #[Title('Идентификатор')]
    #[MinLength(32)]
    #[MaxLength(32)]
    protected ColumnString $uid;
    #[Title('Дата создания')]
    protected ColumnDatetime $createAt;
}
