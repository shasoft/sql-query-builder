<?php

namespace Shasoft\SqlQueryBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Shasoft\DbSchemaDev\Table\User;
use Shasoft\PsrCache\CacheItemPool;
use Shasoft\SqlQueryBuilder\Builder;
use Psr\Cache\CacheItemPoolInterface;
use Shasoft\DbSchemaDev\Table\Article;
use Shasoft\DbTool\DbToolPdoLog;
use Shasoft\SqlQueryBuilder\Command\From;
use Shasoft\SqlQueryBuilder\Command\Join;
use Shasoft\SqlQueryBuilder\Command\Where;
use Shasoft\SqlQueryBuilder\Command\JoinItem;
use Shasoft\SqlQueryBuilder\Tests\ExampleBase;
use Shasoft\PsrCache\Adapter\CacheAdapterArray;
use Shasoft\SqlQueryBuilder\Tests\Table\Comment;
use Shasoft\SqlQueryBuilder\Tests\Table\TabNoPrimaryKey;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillColumnNotPrimaryKey;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillTheJoinMustBeMadePrimaryKeyColumns;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin;

class FillTest extends TestCase
{
    static protected ?CacheItemPoolInterface $cache = null;
    static protected Builder $builderG;
    protected Builder $builder;
    public static function setUpBeforeClass(): void
    {
        self::$cache = new CacheItemPool(new CacheAdapterArray());
        self::$builderG = ExampleBase::createBuilder('mysql', true, self::$cache);
    }
    //
    public function setUp(): void
    {
        parent::setUp();
        $this->builder = self::$builderG;
    }
    // Колонка `{$name}` таблицы `{$tableClass}` не входит в первичный ключ
    public function testBuilderExceptionFillTheJoinMustBeMadePrimaryKeyColumns()
    {
        //
        $this->expectException(BuilderExceptionFillTheJoinMustBeMadePrimaryKeyColumns::class);
        // Список строк с идентификаторами комментариев
        $rows = [
            ['id' => 1],
            ['id' => 3],
            ['id' => 5],
            ['id' => 3],
        ];
        $fill = $this->builder
            ->fill($rows)
            ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                // Выбрать автора комментария
                $fromComment->joinTable(
                    User::class,
                    ['age' => 'userId'],
                    ['id as authorId', 'name as author']
                );
            });
    }
    // В соединении участвуют не все поля первичного индекса
    public function testBuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin1()
    {
        //
        $this->expectException(BuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin::class);
        // Список строк с идентификаторами комментариев
        $rows = [
            ['id' => 1],
            ['id' => 3],
            ['id' => 5],
            ['id' => 3],
        ];
        $fill = $this->builder
            ->fill($rows)
            ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                // Выбрать автора комментария
                $fromComment->joinTable(
                    User::class,
                    [],
                    ['id as authorId', 'name as author']
                );
            });
    }
    // В соединении участвуют не все поля первичного индекса
    public function testBuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin2()
    {
        //
        $this->expectException(BuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin::class);
        // Список строк с идентификаторами комментариев
        $rows = [
            ['id' => 1],
            ['id' => 3],
            ['id' => 5],
            ['id' => 3],
        ];
        $fill = $this->builder
            ->fill($rows)
            ->fromTable(Comment::class, ['id', 'text'], ['text'], function (From $fromComment) {
                // Выбрать автора комментария
                $fromComment->joinTable(
                    User::class,
                    ['id' => 'userId'],
                    ['id as authorId', 'name as author']
                );
            });
    }
    // 
    public function testFill1()
    {
        self::$cache->clear();
        // Список строк с идентификаторами комментариев
        DbToolPdoLog::clear();
        for ($i = 0; $i < 3; $i++) {
            $rows = [
                ['id' => 1]
            ];
            $fill = $this->builder
                ->fill($rows)
                ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                    // Выбрать автора комментария
                    $fromComment->joinTable(
                        User::class,
                        ['id' => 'userId'],
                        ['id as authorId', 'name as author']
                    );
                });
        }
        $sqlList = DbToolPdoLog::getRaw();
        self::assertCount(2, $sqlList);
    }
    // 
    public function testFill2()
    {
        // Список строк с идентификаторами комментариев
        DbToolPdoLog::clear();
        $steps = 3;
        for ($i = 0; $i < $steps; $i++) {
            self::$cache->clear();
            $rows = [
                ['id' => 1]
            ];
            $fill = $this->builder
                ->fill($rows)
                ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                    // Выбрать автора комментария
                    $fromComment->joinTable(
                        User::class,
                        ['id' => 'userId'],
                        ['id as authorId', 'name as author']
                    );
                });
        }
        $sqlList = DbToolPdoLog::getRaw();
        self::assertCount(2 * $steps, $sqlList);
    }
    // 
    public function testFillNoCache()
    {
        self::$cache->clear();
        // Список строк с идентификаторами комментариев
        DbToolPdoLog::clear();
        for ($i = 0; $i < 3; $i++) {
            $rows = [
                ['id' => 1]
            ];
            $fill = $this->builder
                ->fill($rows)
                ->cacheOff()
                ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                    // Выбрать автора комментария
                    $fromComment->joinTable(
                        User::class,
                        ['id' => 'userId'],
                        ['id as authorId', 'name as author']
                    );
                });
        }
        $sqlList = DbToolPdoLog::getRaw();
        self::assertCount(3, $sqlList);
    }
    // 
    public function testFillUpdateCache1()
    {
        self::$cache->clear();
        // Список строк с идентификаторами комментариев
        DbToolPdoLog::clear();
        //
        $rows = [
            ['id' => 1]
        ];
        $fill = $this->builder
            ->fill($rows)
            ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                // Выбрать автора комментария
                $fromComment->joinTable(
                    User::class,
                    ['id' => 'userId'],
                    ['id as authorId', 'name as author']
                );
            });
        //
        $this->builder
            ->update(User::class)
            ->values(['name' => 'Shasoft'])
            ->where(function (Where $where) use ($rows) {
                $where->cond('id', '=', $rows[0]['authorId']);
            })
            ->exec();
        //
        $rows = [
            ['id' => 1]
        ];
        $fill = $this->builder
            ->fill($rows)
            ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                // Выбрать автора комментария
                $fromComment->joinTable(
                    User::class,
                    ['id' => 'userId'],
                    ['id as authorId', 'name as author']
                );
            });
        //
        $sqlList = DbToolPdoLog::getRaw();
        //s_dump($sqlList, $rows);
        self::assertCount(5, $sqlList);
    }
    // 
    public function testFillUpdateCache2()
    {
        self::$cache->clear();
        // Список строк с идентификаторами комментариев
        DbToolPdoLog::clear();
        //
        $rows = [
            ['id' => 1]
        ];
        $fill = $this->builder
            ->fill($rows)
            ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                // Выбрать автора комментария
                $fromComment->joinTable(
                    User::class,
                    ['id' => 'userId'],
                    ['id as authorId', 'name as author']
                );
            });
        //
        $this->builder
            ->update(Comment::class)
            ->values(['text' => 'Article title'])
            ->where(function (Where $where) use ($rows) {
                $where->cond('id', '=', 1);
            })
            ->exec();
        //
        $rows = [
            ['id' => 1]
        ];
        $fill = $this->builder
            ->fill($rows)
            ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                // Выбрать автора комментария
                $fromComment->joinTable(
                    User::class,
                    ['id' => 'userId'],
                    ['id as authorId', 'name as author']
                );
            });
        //
        $sqlList = DbToolPdoLog::getRaw();
        //s_dump($sqlList, $rows);
        self::assertCount(5, $sqlList);
    }
    // 
    public function testFillUpdateCache3()
    {
        self::$cache->clear();
        // Список строк с идентификаторами комментариев
        DbToolPdoLog::clear();
        //
        $rows = [
            ['id' => 1]
        ];
        $fill = $this->builder
            ->fill($rows)
            ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                // Выбрать автора комментария
                $fromComment->joinTable(
                    User::class,
                    ['id' => 'userId'],
                    ['id as authorId', 'name as author']
                );
            });
        //
        $userId = 1;
        while ($userId == $rows[0]['authorId']) {
            $userId++;
        }
        //
        $this->builder
            ->update(Comment::class)
            ->values(['userId' => $userId])
            ->where(function (Where $where) use ($rows) {
                $where->cond('id', '=', 1);
            })
            ->exec();
        //
        $rows = [
            ['id' => 1]
        ];
        $fill = $this->builder
            ->fill($rows)
            ->fromTable(Comment::class, ['id'], ['text'], function (From $fromComment) {
                // Выбрать автора комментария
                $fromComment->joinTable(
                    User::class,
                    ['id' => 'userId'],
                    ['id as authorId', 'name as author']
                );
            });
        //
        $sqlList = DbToolPdoLog::getRaw();
        /*
            "SELECT * FROM `shasoft-sqlquerybuilder-tests-table-comment` WHERE `id` = 1"
            "SELECT * FROM `shasoft-dbschemadev-table-user` WHERE `id` = 1"
            "UPDATE `shasoft-sqlquerybuilder-tests-table-comment` `COMMENT_1` SET `userId`=2 WHERE `COMMENT_1`.`id` = 1"
            "SELECT `COMMENT_1`.`id` FROM `shasoft-sqlquerybuilder-tests-table-comment` `COMMENT_1` WHERE `COMMENT_1`.`id` = 1"
            "SELECT * FROM `shasoft-sqlquerybuilder-tests-table-comment` WHERE `id` = 1"
            "SELECT * FROM `shasoft-dbschemadev-table-user` WHERE `id` = 2"        
        */
        //s_dump($sqlList, $rows);
        self::assertCount(6, $sqlList);
    }
}
