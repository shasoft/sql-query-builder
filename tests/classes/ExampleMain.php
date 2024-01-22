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

class ExampleMain extends ExampleBase
{
    // Тесты
    public function get(): array
    {
        return [
            '~habr' => function (Builder $builder) {
                $builder->select(User::class, ['name'])
                    ->name('main') // Устанавливаем имя контекста
                    ->where(function (Where $where) {
                        $where->inSelect(
                            'id',
                            Article::class,
                            'userId',
                            function (Select $select) {
                                $select
                                    ->where(function (Where $where) {
                                        $where->cond(
                                            'id',
                                            '=',
                                            // Используемый именованный контекст
                                            $where->column('id', 'main')
                                        );
                                    });
                            }
                        );
                    })
                    ->get();
            },
            'Demo' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['id', 'name'])
                    ->where(function (Where $where) {
                        $where
                            ->cond('id', '>', 1)
                            ->inSelect(
                                ['id', 'name'],
                                UserInfo::class,
                                ['id', 'description']
                            );
                    })
                    ->join(function (Join $join) {
                        $join->left(Article::class, [
                            'userId' => 'id'
                        ], [
                            'sum(rate) as rate'
                        ])
                            ->having('SUM(rate)', '>', 100);
                    })
                    ->limit(2)->orderBy('name')
                    ->groupBy('id')->groupBy('name')
                    ->get();
            },
            'Select' => function (Builder $builder) {
                $builder->select(User::class)->get();
            },
            'Select(Alias Column)' => function (Builder $builder) {
                $builder
                    ->select(User::class, [
                        'id',
                        'name' => 'asName',
                        'age as asAge',
                        'role As asRole'
                    ])
                    ->get();
            },
            'Select+Where+Like' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['name'])
                    ->where(function (Where $where): void {
                        $where
                            ->like('name', 'I%');
                    })
                    ->get();
            },
            'Select+Where+Not Like' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['name'])
                    ->where(function (Where $where): void {
                        $where
                            ->notLike('name', 'I%');
                    })
                    ->get();
            },
            'Select+Where+Where 1' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['name'])
                    ->where(function (Where $where): void {
                        $where
                            ->or()
                            ->cond('id', '=', 4)
                            ->cond('id', '=', 5)
                            ->where(function (Where $where) {
                                $where
                                    ->and()
                                    ->cond('id', '>', 3)
                                    ->cond('id', '<', 6);
                            });
                    })
                    ->get();
            },
            'Select+Where+Where 2' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['name'])
                    ->where(function (Where $where): void {
                        $where
                            ->and()
                            ->cond('id', '=', 4)
                            ->cond('id', '=', 5)
                            ->where(function (Where $where) {
                                $where
                                    ->or()
                                    ->cond('id', '>', 3)
                                    ->cond('id', '<', 6);
                            });
                    })->get();
            },
            'Select+Where+=Null' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['name'])
                    ->where(function (Where $where): void {
                        $where
                            ->or()
                            ->cond('id', '=', null);
                    })
                    ->get();
            },
            'Select+Where+Null' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['name'])
                    ->where(function (Where $where): void {
                        $where
                            ->or()
                            ->cond('id', '=', 4)
                            ->isNull('name');
                    })
                    ->get();
            },
            'Select+Where+NotNull' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['name'])
                    ->where(function (Where $where): void {
                        $where
                            ->or()
                            ->cond('id', '=', 4)
                            ->isNotNull('name');
                    })
                    ->get();
            },
            'Select+Where+Between' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['id'])
                    ->where(function (Where $where): void {
                        $where
                            ->between('id', 2, 7);
                    })
                    ->get();
            },
            'Select+Where+Not Between' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['id'])
                    ->where(function (Where $where): void {
                        $where
                            ->notBetween('id', 2, 7);
                    })
                    ->get();
            },
            'Select+Join+Inner' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['title'])
                    ->join(function (Join $join): void {
                        // Соединение с таблицей User
                        $join
                            ->inner(User::class, ['id' => 'userId'], ['name'])
                            ->where(function (Where $where) {
                                $where->cond('id', '>', 3);
                            });
                    })->get();
            },
            'Select+Join+Left' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['title'])
                    ->join(function (Join $join): void {
                        // Соединение с таблицей User
                        $join
                            ->left(User::class, ['id' => 'userId'], ['name'])
                            ->where(function (Where $where) {
                                $where->cond('id', '>', 3);
                            });
                    })
                    ->get();
            },
            'Select+Join+Right' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['title'])
                    ->join(function (Join $join): void {
                        // Соединение с таблицей User
                        $join
                            ->right(User::class, ['id' => 'userId'], ['name'])
                            ->where(function (Where $where) {
                                $where->cond('id', '>', 3);
                            });
                    })
                    ->get();
            },
            'Select+Join(3)' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['title'])
                    ->where(function (Where $where): void {
                        $where->cond('id', '=', 4)->cond('id', '=', 5);
                    })
                    ->join(function (Join $join): void {
                        // Соединение с таблицей User
                        $joinUser = $join
                            ->left(User::class, ['id' => 'userId'], ['name'])
                            ->where(function (Where $where) {
                                $where->cond('id', '>', 3);
                            });
                        // Соединение таблицы User из соединения выше с таблицей User
                        $joinUser
                            ->left(User::class, ['id' => 'id'], ['name' => 'name2'])
                            ->where(function (Where $where) {
                                $where->cond('id', '!=', 33);
                            });
                        // Соединение с таблицей UserInfo
                        $join
                            ->left(UserInfo::class, ['id' => $joinUser->column('id')])
                            ->where(function (Where $where) {
                                $where->cond('id', '!=', 7);
                            });
                    })
                    ->get();
            },
            'Select+Join+OrderBy 1' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['title'])
                    ->where(function (Where $where): void {
                        $where->cond('id', '=', 4)->cond('id', '=', 5);
                    })
                    ->join(function (Join $join): void {
                        // Соединение с таблицей User
                        $join
                            ->left(User::class, ['id' => 'userId'], ['name'])
                            ->where(function (Where $where) {
                                $where->cond('id', '>', 3);
                            })
                            ->orderBy('id');
                    })
                    ->get();
            },
            'Select+Join+OrderBy 2' => function (Builder $builder) {
                $select = $builder
                    ->select(Article::class, ['title'])
                    ->name('article');
                $select
                    ->where(function (Where $where): void {
                        $where->cond('id', '=', 4)->cond('id', '=', 5);
                    })
                    ->join(function (Join $join): void {
                        // Соединение с таблицей User
                        $join
                            ->left(User::class, ['id' => 'userId'], ['name'])
                            ->name('user')
                            ->where(function (Where $where) {
                                $where
                                    // Добавим условия по полю 
                                    // из сохраненного контекста article
                                    ->cond(
                                        $where->column('id', 'article'),
                                        '=',
                                        7
                                    )
                                    ->cond(
                                        'id',
                                        '=',
                                        $where->column('id', 'article')
                                    );
                            });
                    })
                    // Добавить сортировку по полю 
                    // сохраненного именованного контекста user
                    ->orderBy($select->column('id', 'user'))
                    ->get();
            },
            'Select+Join' => function (Builder $builder) {
                $rows = $builder
                    ->select(Comment::class, ['id', 'createAt'])
                    ->where(function (Where $where) {
                        $where->inArray('id', [1, 3, 5]);
                    })
                    ->join(function (Join $join) {
                        $joinComment = $join->left(Article::class, ['id' => 'articleId'], ['title']);
                        $joinUserArticle = $join->left(User::class, ['id' => $join->column('userId')], ['name as authorArticleName']);
                        $joinUserComment = $join->left(User::class, ['id' => $joinComment->column('userId')], ['name as authorCommentName']);
                    })
                    ->get();
            },
            'Select+Relation' => function (Builder $builder) {
                $rows = $builder
                    ->select(Comment::class, ['id', 'createAt'])
                    ->where(function (Where $where) {
                        $where->inArray('id', [1, 3, 5]);
                    })
                    ->relation('article', ['title'], function (JoinItem $joinItem) {
                        $joinItem->relation('author', ['name as authorArticleName']);
                    })
                    ->relation('author', ['name as authorCommentName'])
                    ->get();
            },
            'Select+Sum' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['sum(age)'])
                    ->where(function (Where $where): void {
                        $where->or()->cond('id', '=', 4)->cond('id', '=', 5);
                    })
                    ->get();
            },
            'Select+Sum(Alias Column)' => function (Builder $builder) {
                $builder
                    ->select(User::class, [
                        'sum(age) as sumAge',
                        'avg(age)' => 'avgAge'
                    ])
                    ->where(function (Where $where): void {
                        $where->or()->cond('id', '=', 4)->cond('id', '=', 5);
                    })
                    ->get();
            },
            'Select+Limit 1' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['id', 'name'])
                    ->limit(10, 5)
                    ->get();
            },
            'Select+Limit 2' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['id', 'name'])
                    ->limit(9)
                    ->get();
            },
            'Select+Distinct' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['userId'])
                    ->Distinct()
                    ->get();
            },
            'Select+OrderBy' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['id', 'name'])
                    ->OrderBy('id', false)
                    ->OrderBy('name', true)
                    ->get();
            },
            'Select+GroupBy' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['userId'])
                    ->GroupBy('userId')
                    ->get();
            },
            'Select+Having' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['userId', 'count(userId)' => 'cnt'])
                    ->GroupBy('userId')
                    ->having('count(userId)', '>', 1)
                    ->get();
            },
            'Select+Having+Between' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['userId', 'count(userId)' => 'cnt'])
                    ->GroupBy('userId')
                    ->having('count(userId)', 'between', [2, 5])
                    ->get();
            },
            'Select+Having+Not Between' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['userId', 'count(userId)' => 'cnt'])
                    ->GroupBy('userId')
                    ->having('count(userId)', '   not    between  ', [2, 5])
                    ->get();
            },
            'Select+In(Array)' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['name'])
                    ->where(function (Where $where) {
                        $where->inArray('id', [3, 4]);
                    })
                    ->get();
            },
            'Select+In(Select)' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['name'])
                    ->where(function (Where $where) {
                        $where->inSelect('id', Article::class, 'userId', function (Select $select) {
                            $select
                                ->where(function (Where $where) {
                                    $where->cond('id', '=', 3);
                                });
                        });
                    })
                    ->get();
            },
            'Select+In(Select)+MultiColumns' => function (Builder $builder) {
                $builder
                    ->select(User::class, ['name'])
                    ->where(function (Where $where) {
                        $where->inSelect(
                            ['id', 'name'],
                            Article::class,
                            ['userId', 'title'],
                            function (Select $select) {
                                $select
                                    ->where(function (Where $where) {
                                        $where->cond('id', '=', 2);
                                    });
                            }
                        );
                    })
                    ->get();
            },
            'Select+In(Select)+Function' => function (Builder $builder) {
                $builder
                    ->select(Article::class, ['id', 'userId'])
                    ->where(function (Where $where) {
                        $where->inSelect('userId', Article::class, 'min(userId)');
                    })
                    ->get();
            },
            'Select+In(Select)+Context.Name' => function (Builder $builder) {
                $builder->select(User::class, ['name'])
                    ->name('main')
                    ->where(function (Where $where) {
                        $where->inSelect('id', Article::class, 'userId', function (Select $select) {
                            $select
                                ->where(function (Where $where) {
                                    $where->cond('id', '=', $where->column('id', 'main'));
                                });
                        });
                    })
                    ->get();
            },
            'Insert+Values(Array)' => function (Builder $builder) {
                $builder
                    ->insert(User::class)
                    ->values([
                        'name' => 'Igor',
                        'age' => ColumnInteger::random(18, 88),
                        'role' => 'Moderator'
                    ])
                    ->exec();
            },
            'Insert+Values(Closure)' => function (Builder $builder) {
                $builder
                    ->insert(User::class)
                    ->values(function (Values $values) {
                        $values->value('name', 'Alex');
                        $values->valueSelect('age', User::class, 'id', function (Select $select) {
                            $select->where(function (Where $where) {
                                $where->cond('id', '=', 8);
                            });
                        });
                        $values->value('role', 'Vip');
                    })
                    ->exec();
            },
            'Delete' => function (Builder $builder) {
                $builder
                    ->delete(User::class)

                    ->where(function (Where $where) {
                        $where->cond('id', '>', 3);
                    })
                    ->exec();
            },
            'Delete+In(Array) 1' => function (Builder $builder) {
                $builder
                    ->delete(User::class)
                    ->where(function (Where $where) {
                        $where->inArray('id', [3, 4]);
                    })
                    ->exec();
            },
            'Delete+In(Array) 2' => function (Builder $builder) {
                $builder
                    ->delete(User::class)
                    ->where(function (Where $where) {
                        $where->inArray('id', [5]);
                    })
                    ->exec();
            },
            'Update' => function (Builder $builder) {
                $builder
                    ->update(User::class)
                    ->where(function (Where $where) {
                        $where->cond('id', '=', 3);
                    })
                    ->values(
                        [
                            'name' => 'Admin',
                            'age' => 7
                        ]
                    )
                    ->exec();
            },
            'Update+In(Array)' => function (Builder $builder) {
                $builder
                    ->update(User::class)
                    ->where(function (Where $where) {
                        $where->inArray('id', [1, 2, 3]);
                    })
                    ->values(
                        [
                            'name' => 'Admin',
                            'age' => 7
                        ]
                    )
                    ->exec();
            },
            'Update+In(Select)' => function (Builder $builder) {
                $builder
                    ->update(Article::class)
                    ->where(function (Where $where) {
                        $where->inArray('id', [1, 2, 3]);
                    })
                    ->values(function (Values $values) {
                        $values->valueSelect(
                            'title',
                            User::class,
                            'name',
                            function (Select $select) {
                                $select->limit(1);
                            }
                        );
                    })
                    ->exec();
            },
            'Select+Pagination' => function (Builder $builder) {
                $builder->select(User::class, ['id', 'name'])->pagination(2, 3);
            },
            'Select+Where+When' => function (Builder $builder) {
                $builder->select(User::class, ['id', 'name'])->where(function (Where $where) {
                    for ($id = 1; $id <= 8; $id++) {
                        // Фильтрация по условию
                        $where->when($id % 2 == 1, function (Where $where) use ($id) {
                            $where->cond('id', '=', $id);
                        });
                    }
                })->get();
            },
            '' => function (Builder $builder) {
                /*
                $builder
                    ->select(Table::class, ['column1', 'column2',...])
                    ->where(function (Where $where) {
                        $where
                            ->cond('id', '>', 1)
                            ->inSelect(
                                ['id', 'name'],
                                UserInfo::class,
                                ['id', 'description']
                            )->where(function(Where $where) {
                                // вложенный блок
                                // ...
                            });
                    })
                    ->join(function (Join $join) {
                        $join->left(Article::class, [
                            'userId' => 'id'
                        ], [
                            'sum(rate) as rate'
                        ])
                            ->having('SUM(rate)', '>', 100);
                    })
                    ->limit(2,)        //
                    ->orderBy(columnName) // Сортировка
                    ->groupBy('id')   // Группировка
                    ->get();
                    //*/
            },
        ];
    }
}
