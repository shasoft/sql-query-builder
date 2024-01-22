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
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Column\ColumnDatetime;
use Shasoft\DbSchema\Command\NumberOfSpaces;
use Shasoft\DbSchema\Relation\RelationManyToOne;
use Shasoft\DbSchema\Relation\RelationOneToMany;
use Shasoft\SqlQueryBuilder\MigrationCommand\CacheNone;

//#[Migration('2010-01-18T12:00:00+03:00')]
#[Title('Комментарии')]
class Comment
{
    //
    #[Title('Идентификатор')]
    protected ColumnId $id;
    #[Title('Ссылка на родительский комментарий')]
    #[ReferenceTo(User::class, 'id')]
    protected Reference $parentId;
    #[Title('Ссылка на автора')]
    #[ReferenceTo(User::class, 'id')]
    protected Reference $userId;
    #[Title('Ссылка на статью')]
    #[ReferenceTo(User::class, 'id')]
    protected Reference $articleId;
    #[Title('Текст комментария')]
    #[MinLength(8)]
    #[MaxLength(128)]
    #[NumberOfSpaces(6)]
    protected ColumnString $text;
    #[Title('Дата создания')]
    protected ColumnDatetime $createAt;
    #[Title('Рейтинг')]
    #[MinValue(0)]
    #[MaxValue(1000000)]
    #[CacheNone]
    protected ColumnInteger $rate;
    #[Columns('id')]
    protected IndexPrimary $pkId;
    // Отношение
    #[RelTableTo(self::class)]
    #[RelNameTo('children')]
    #[Columns(['parentId' => 'id'])]
    #[Title('Родительский комментарий')]
    protected RelationManyToOne $parent;
    // Отношение
    #[RelTableTo(User::class)]
    #[RelNameTo('comments')]
    #[Columns(['userId' => 'id'])]
    #[Title('Автор')]
    protected RelationManyToOne $author;
    // Отношение
    #[RelTableTo(Article::class)]
    #[RelNameTo('comments')]
    #[Columns(['articleId' => 'id'])]
    #[Title('Статья')]
    protected RelationManyToOne $article;
}
