<?php

namespace Shasoft\SqlQueryBuilder\Tests;

use Throwable;
use Shasoft\DbTool\DbToolPdo;
use Shasoft\DbTool\DbToolPdoLog;
use Shasoft\DbSchemaDev\Table\User;
use Shasoft\DbTool\DbToolSqlFormat;
use Shasoft\PsrCache\CacheItemPool;
use Shasoft\SqlQueryBuilder\Builder;
use Shasoft\DbSchemaDev\Table\Article;
use Shasoft\DbSchemaDev\Table\TabTest;
use Shasoft\DbSchemaDev\Table\UserInfo;
use Shasoft\DbSchema\DbSchemaMigrations;
use Shasoft\DbSchemaDev\DbSchemaDevTool;
use Shasoft\SqlQueryBuilder\Command\From;
use Shasoft\SqlQueryBuilder\Command\Join;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\SqlQueryBuilder\Command\Where;
use Shasoft\SqlQueryBuilder\Command\Select;
use Shasoft\SqlQueryBuilder\Command\Values;
use Shasoft\SqlQueryBuilder\Command\JoinItem;
use Shasoft\SqlQueryBuilder\Command\Relation;
use Shasoft\PsrCache\Adapter\CacheAdapterArray;
use Shasoft\DbSchema\Driver\DbSchemaDriverMySql;
use Shasoft\SqlQueryBuilder\Tests\Table\Comment;
use Shasoft\DbSchema\Driver\DbSchemaDriverPostgreSql;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionSqlError;

class ExampleFill extends ExampleBase
{
    // Тесты
    public function get(): array
    {
        return [
            '~debug' => function (Builder $builder) {
                // Список строк с идентификаторами комментариев
                $rows = [
                    ['id' => 1]
                ];
                DbToolPdoLog::clear();
                $fill = $builder
                    ->fill($rows)
                    ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                        // Выбрать автора комментария
                        $fromComment->joinTable(
                            User::class,
                            ['id' => 'userId'],
                            ['name as authorCommentName']
                        );
                    });
                $sqlList = DbToolPdoLog::getRaw();
                s_dump($sqlList);
            },
            'FillSimple' => function (Builder $builder) {
                // Список строк с идентификаторами комментариев
                $rows = [
                    ['id' => 1],
                    ['id' => 3],
                    ['id' => 5],
                    ['id' => 3],
                ];
                $fill = $builder
                    ->fill($rows)
                    ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                        // Выбрать автора комментария
                        $fromComment->joinTable(
                            User::class,
                            ['id' => 'userId'],
                            ['name as authorCommentName']
                        );
                        // Выбрать родительскую статью
                        $fromUserArticle = $fromComment->joinTable(
                            Article::class,
                            ['id' => 'articleId'],
                            ['title']
                        );
                        // Выбрать автора статьи
                        $fromUserArticle->joinTable(
                            User::class,
                            ['id' => 'userId'],
                            ['name as authorArticleName']
                        );
                    });
                //s_dump($fill, $rows);
            },
            'FillSimple+Relation' => function (Builder $builder) {
                // Список строк с идентификаторами комментариев
                $rows = [
                    ['id' => 1],
                    ['id' => 3],
                    ['id' => 5],
                    ['id' => 3],
                ];
                $fill = $builder
                    ->fill($rows)
                    ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                        // Выбрать автора комментария
                        $fromComment->joinRelation(
                            'author',
                            ['name as authorCommentName']
                        );
                        // Выбрать родительскую статью
                        $fromUserArticle = $fromComment->joinRelation(
                            'article',
                            ['title']
                        );
                        // Выбрать автора статьи
                        $fromUserArticle->joinRelation(
                            'author',
                            ['name as authorArticleName']
                        );
                    });
                //s_dump($fill, $rows);
            },
            'Fill+Null' => function (Builder $builder) {
                // Список строк с идентификаторами комментариев
                $rows = [
                    ['id' => 9],
                    ['id' => 13],
                ];
                $fill = $builder
                    ->fill($rows)
                    ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                        // Выбрать автора комментария
                        $fromComment->joinTable(
                            User::class,
                            ['id' => 'userId'],
                            ['name as authorCommentName']
                        );
                    });
                s_dump($fill, $rows);
            },
            'FillSimple+NoCache' => function (Builder $builder) {
                // Список строк с идентификаторами комментариев
                $rows = [
                    ['id' => 1],
                    ['id' => 3],
                    ['id' => 5],
                    ['id' => 3],
                ];
                $fill = $builder
                    ->fill($rows)
                    ->cacheOff()
                    ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                        // Выбрать родительскую статью
                        $fromUserArticle = $fromComment->joinTable(
                            Article::class,
                            ['id' => 'articleId'],
                            ['title']
                        );
                        // Выбрать автора комментария
                        $fromComment->joinTable(
                            User::class,
                            ['id' => $fromComment->column('userId')],
                            ['name as authorCommentName']
                        );
                        // Выбрать автора статьи
                        $fromComment->joinTable(
                            User::class,
                            ['id' => $fromUserArticle->column('userId')],
                            ['name as authorArticleName']
                        );
                    });
                //s_dump($fill, $rows);
            },
            '~Select+Join' => function (Builder $builder) {
                //-- Стандартная выборка через JOIN
                $select = $builder
                    ->select(Comment::class, ['id'])
                    ->where(function (Where $where) {
                        $where->inArray('id', [1, 3, 5]);
                    })
                    ->join(function (Join $join) {
                        $joinComment = $join->left(Comment::class, ['id' => 'parentId'], ['id as parent']);
                        $root = $join->left(Comment::class, ['id' => $joinComment->column('parentId')], ['id as root']);
                        $join->left(User::class, ['id' => $root->column('userId')], ['name as authorNameRoot']);
                        $joinUserArticle = $join->left(Article::class, ['id' => 'articleId'], ['title']);
                        $join->left(User::class, ['id' => $join->column('userId')], ['name as authorArticleName']);
                        $join->left(User::class, ['id' => $joinUserArticle->column('userId')], ['name as authorCommentName']);
                    });
                $rows = $select->get();
                //s_dump($select, $rows);
            },
            'Fill+NoCache' => function (Builder $builder) {
                // Список строк с идентификаторами комментариев
                $rows = [
                    ['id' => 1],
                    ['id' => 3],
                    ['id' => 5],
                    ['id' => 3],
                ];
                $fill = $builder
                    ->fill($rows)
                    ->cacheOff()
                    ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                        $fromComment = $fromComment->joinTable(Comment::class, ['id' => 'parentId'], ['id as parent']);
                        $rootComment = $fromComment->joinTable(Comment::class, ['id' => $fromComment->column('parentId')], ['id as root']);
                        $fromComment->joinTable(User::class, ['id' => $rootComment->column('userId')], ['name as authorNameRoot']);
                        $fromUserArticle = $fromComment->joinTable(Article::class, ['id' => 'articleId'], ['title']);
                        $fromComment->joinTable(User::class, ['id' => $fromComment->column('userId')], ['name as authorArticleName']);
                        $fromComment->joinTable(User::class, ['id' => $fromUserArticle->column('userId')], ['name as authorCommentName']);
                    });
                //s_dump($fill, $rows);
            },
            'Fill 1' => function (Builder $builder) {
                // Список строк с идентификаторами комментариев
                $rows = [
                    ['id' => 1],
                    ['id' => 3],
                    ['id' => 5],
                    ['id' => 3],
                ];
                $fill = $builder
                    ->fill($rows)
                    ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment1) {
                        $fromComment2 = $fromComment1->joinTable(Comment::class, ['id' => 'parentId'], ['id as parent']);
                        $fromComment3 = $fromComment2->joinTable(Comment::class, ['id' => $fromComment1->column('parentId')], ['id as root']);
                        $fromComment1->joinTable(User::class, ['id' => $fromComment3->column('userId')], ['name as authorNameRoot']);
                        $fromUserArticle = $fromComment1->joinTable(Article::class, ['id' => 'articleId'], ['title']);
                        $fromComment1->joinTable(User::class, ['id' => $fromComment1->column('userId')], ['name as authorArticleName']);
                        $fromComment1->joinTable(User::class, ['id' => $fromUserArticle->column('userId')], ['name as authorCommentName']);
                    });
            },
            'Fill 2' => function (Builder $builder) {
                // Список строк с идентификаторами комментариев
                $rows = [
                    ['id' => 1],
                    ['id' => 3],
                    ['id' => 5],
                    ['id' => 3],
                    ['id' => 7], // Добавился один новый идентификатор
                ];
                $fill = $builder
                    ->fill($rows)
                    ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment1) {
                        $fromComment2 = $fromComment1->joinTable(Comment::class, ['id' => 'parentId'], ['id as parent']);
                        $fromComment3 = $fromComment2->joinTable(Comment::class, ['id' => $fromComment1->column('parentId')], ['id as root']);
                        $fromComment1->joinTable(User::class, ['id' => $fromComment3->column('userId')], ['name as authorNameRoot']);
                        $fromUserArticle = $fromComment1->joinTable(Article::class, ['id' => 'articleId'], ['title']);
                        $fromComment1->joinTable(User::class, ['id' => $fromComment1->column('userId')], ['name as authorArticleName']);
                        $fromComment1->joinTable(User::class, ['id' => $fromUserArticle->column('userId')], ['name as authorCommentName']);
                    });
            },
            '' => function (Builder $builder) {
            },
        ];
    }
}
