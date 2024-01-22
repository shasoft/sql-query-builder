<?php

namespace Shasoft\SqlQueryBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Shasoft\SqlQueryBuilder\Builder;
use Shasoft\DbSchemaDev\Table\Article;
use Shasoft\SqlQueryBuilder\Command\Join;
use Shasoft\SqlQueryBuilder\Command\Where;
use Shasoft\SqlQueryBuilder\Command\JoinItem;
use Shasoft\SqlQueryBuilder\Tests\ExampleBase;
use Shasoft\SqlQueryBuilder\Tests\Table\Comment;
use Shasoft\SqlQueryBuilder\Tests\Table\TabNoPrimaryKey;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillCommandNotSupport;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillColumnNotPrimaryKey;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillPrimaryKeyIsMissing;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillTheJoinMustBeMadePrimaryKeyColumns;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillItIsNecessaryToSpecifyTheFilteringConditions;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillOnlyOneFilterConditionPerColumnIsAllowed;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillFilteringConditionsAreNotAvailableInJoins;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionFillFilteringConditionsMustBeBasedOnOnlyOneTable;

class FillT_est extends TestCase
{
    static protected Builder $builderG;
    protected Builder $builder;
    public static function setUpBeforeClass(): void
    {
        self::$builderG = ExampleBase::createBuilder('mysql', false);
    }
    //
    public function setUp(): void
    {
        parent::setUp();
        $this->builder = self::$builderG;
    }
    // Колонка `{$name}` таблицы `{$tableClass}` не входит в первичный ключ
    public function testBuilderExceptionFillColumnNotPrimaryKey()
    {
        //
        $this->expectException(BuilderExceptionFillColumnNotPrimaryKey::class);
        //
        $this->builder
            ->selectCache(Comment::class, ['id', 'createAt'])
            ->where(function (Where $where) {
                $where->cond('articleId', '=', 1);
            });
    }
    // Команда `{$command}` не поддерживается при выполнении выборки с КЭШированием
    public function testBuilderExceptionFillCommandNotSupport()
    {
        //
        $this->expectException(BuilderExceptionFillCommandNotSupport::class);
        //
        $this->builder
            ->selectCache(Comment::class, ['id', 'createAt'])
            ->where(function (Where $where) {
                $where->cond('id', '>', 1);
            });
    }
    // Условия фильтрации должны быть только по одной таблице
    public function testBuilderExceptionFillFilteringConditionsAreNotAvailableInJoins()
    {
        //
        $this->expectException(BuilderExceptionFillFilteringConditionsAreNotAvailableInJoins::class);
        //
        $this->builder
            ->selectCache(Comment::class, ['id', 'createAt'])
            ->where(function (Where $where) {
                $where->cond('id', '=', 1);
            })
            ->relation('author', ['name as authorCommentName'], function (JoinItem $joinItem) {
                $joinItem->where(function (Where $where) {
                    $where->cond('id', '=', 2);
                });
            });
    }
    public function testBuilderExceptionFillFilteringConditionsMustBeBasedOnOnlyOneTable()
    {
        //
        $this->expectException(BuilderExceptionFillFilteringConditionsMustBeBasedOnOnlyOneTable::class);
        //
        $this->builder
            ->selectCache(Comment::class, ['id', 'createAt'])
            ->relation('author', ['name as authorCommentName'], function (JoinItem $joinItem) {
                $joinItem->name('user');
            })->where(function (Where $where) {
                $where->cond('id', '=', 1);
                $where->cond($where->column('id', 'user'), '=', 2);
            });
    }
    // Допустимо только одно условие фильтрации по колонке `{$name}` таблицы `{$tableClass}`
    public function testBuilderExceptionFillOnlyOneFilterConditionPerColumnIsAllowed()
    {
        //
        $this->expectException(BuilderExceptionFillOnlyOneFilterConditionPerColumnIsAllowed::class);
        //
        $this->builder
            ->selectCache(Comment::class, ['id', 'createAt'])
            ->where(function (Where $where) {
                $where->cond('id', '=', 1);
                $where->inArray('id', [1, 3, 5]);
            });
    }
    // У таблицы `{$tableClass}` отсутствует первичный ключ
    public function testBuilderExceptionFillPrimaryKeyIsMissing()
    {
        //
        $this->expectException(BuilderExceptionFillPrimaryKeyIsMissing::class);
        //
        $this->builder
            ->selectCache(TabNoPrimaryKey::class, ['createAt'])
            ->where(function (Where $where) {
                $where->cond('uid', '=', 'xxx-yyy-zzz');
            });
    }
    // Необходимо обязательно указать условия фильтрации
    public function testBuilderExceptionFillItIsNecessaryToSpecifyTheFilteringConditions()
    {
        //
        $this->expectException(BuilderExceptionFillItIsNecessaryToSpecifyTheFilteringConditions::class);
        //
        $this->builder
            ->selectCache(Article::class, ['createAt'])
            ->get();
    }
    // В соединении участвуют не все поля первичного индекса
    public function testBuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin()
    {
        //
        $this->expectException(BuilderExceptionFillNotAllColumnsOfThePrimaryIndexAreIncludedInTheJoin::class);
        //
        $this->builder
            ->selectCache(Comment::class, ['id', 'createAt'])
            ->where(function (Where $where) {
                $where->inArray('id', [1, 3, 5]);
            })
            ->join(function (Join $join) {
                $join->left(Article::class, [], ['title']);
            });
    }
    // Соединение должно выполняться по ключевым полям
    public function testBuilderExceptionFillTheJoinMustBeMadePrimaryKeyColumns()
    {
        //
        $this->expectException(BuilderExceptionFillTheJoinMustBeMadePrimaryKeyColumns::class);
        //
        $this->builder
            ->selectCache(Comment::class, ['id', 'createAt'])
            ->where(function (Where $where) {
                $where->inArray('id', [1, 3, 5]);
            })
            ->join(function (Join $join) {
                $join->left(Article::class, ['userId' => 'articleId'], ['title']);
            });
    }
}
