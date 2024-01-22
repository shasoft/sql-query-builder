## Sql Query Builder examples
- [Sql Query Builder examples](#sql-query-builder-examples)
  - [Demo](#demo)
    - [mysql](#mysql)
    - [pgsql](#pgsql)
  - [Select](#select)
    - [mysql](#mysql-1)
    - [pgsql](#pgsql-1)
  - [Select(Alias Column)](#selectalias-column)
    - [mysql](#mysql-2)
    - [pgsql](#pgsql-2)
  - [Select+Where+Like](#selectwherelike)
    - [mysql](#mysql-3)
    - [pgsql](#pgsql-3)
  - [Select+Where+Not Like](#selectwherenot-like)
    - [mysql](#mysql-4)
    - [pgsql](#pgsql-4)
  - [Select+Where+Where 1](#selectwherewhere-1)
    - [mysql](#mysql-5)
    - [pgsql](#pgsql-5)
  - [Select+Where+Where 2](#selectwherewhere-2)
    - [mysql](#mysql-6)
    - [pgsql](#pgsql-6)
  - [Select+Where+=Null](#selectwherenull)
    - [mysql](#mysql-7)
    - [pgsql](#pgsql-7)
  - [Select+Where+Null](#selectwherenull-1)
    - [mysql](#mysql-8)
    - [pgsql](#pgsql-8)
  - [Select+Where+NotNull](#selectwherenotnull)
    - [mysql](#mysql-9)
    - [pgsql](#pgsql-9)
  - [Select+Where+Between](#selectwherebetween)
    - [mysql](#mysql-10)
    - [pgsql](#pgsql-10)
  - [Select+Where+Not Between](#selectwherenot-between)
    - [mysql](#mysql-11)
    - [pgsql](#pgsql-11)
  - [Select+Join+Inner](#selectjoininner)
    - [mysql](#mysql-12)
    - [pgsql](#pgsql-12)
  - [Select+Join+Left](#selectjoinleft)
    - [mysql](#mysql-13)
    - [pgsql](#pgsql-13)
  - [Select+Join+Right](#selectjoinright)
    - [mysql](#mysql-14)
    - [pgsql](#pgsql-14)
  - [Select+Join(3)](#selectjoin3)
    - [mysql](#mysql-15)
    - [pgsql](#pgsql-15)
  - [Select+Join+OrderBy 1](#selectjoinorderby-1)
    - [mysql](#mysql-16)
    - [pgsql](#pgsql-16)
  - [Select+Join+OrderBy 2](#selectjoinorderby-2)
    - [mysql](#mysql-17)
    - [pgsql](#pgsql-17)
  - [Select+Join](#selectjoin)
    - [mysql](#mysql-18)
    - [pgsql](#pgsql-18)
  - [Select+Relation](#selectrelation)
    - [mysql](#mysql-19)
    - [pgsql](#pgsql-19)
  - [Select+Sum](#selectsum)
    - [mysql](#mysql-20)
    - [pgsql](#pgsql-20)
  - [Select+Sum(Alias Column)](#selectsumalias-column)
    - [mysql](#mysql-21)
    - [pgsql](#pgsql-21)
  - [Select+Limit 1](#selectlimit-1)
    - [mysql](#mysql-22)
    - [pgsql](#pgsql-22)
  - [Select+Limit 2](#selectlimit-2)
    - [mysql](#mysql-23)
    - [pgsql](#pgsql-23)
  - [Select+Distinct](#selectdistinct)
    - [mysql](#mysql-24)
    - [pgsql](#pgsql-24)
  - [Select+OrderBy](#selectorderby)
    - [mysql](#mysql-25)
    - [pgsql](#pgsql-25)
  - [Select+GroupBy](#selectgroupby)
    - [mysql](#mysql-26)
    - [pgsql](#pgsql-26)
  - [Select+Having](#selecthaving)
    - [mysql](#mysql-27)
    - [pgsql](#pgsql-27)
  - [Select+Having+Between](#selecthavingbetween)
    - [mysql](#mysql-28)
    - [pgsql](#pgsql-28)
  - [Select+Having+Not Between](#selecthavingnot-between)
    - [mysql](#mysql-29)
    - [pgsql](#pgsql-29)
  - [Select+In(Array)](#selectinarray)
    - [mysql](#mysql-30)
    - [pgsql](#pgsql-30)
  - [Select+In(Select)](#selectinselect)
    - [mysql](#mysql-31)
    - [pgsql](#pgsql-31)
  - [Select+In(Select)+MultiColumns](#selectinselectmulticolumns)
    - [mysql](#mysql-32)
    - [pgsql](#pgsql-32)
  - [Select+In(Select)+Function](#selectinselectfunction)
    - [mysql](#mysql-33)
    - [pgsql](#pgsql-33)
  - [Select+In(Select)+Context.Name](#selectinselectcontextname)
    - [mysql](#mysql-34)
    - [pgsql](#pgsql-34)
  - [Insert+Values(Array)](#insertvaluesarray)
    - [mysql](#mysql-35)
    - [pgsql](#pgsql-35)
  - [Insert+Values(Closure)](#insertvaluesclosure)
    - [mysql](#mysql-36)
    - [pgsql](#pgsql-36)
  - [Delete](#delete)
    - [mysql](#mysql-37)
    - [pgsql](#pgsql-37)
  - [Delete+In(Array) 1](#deleteinarray-1)
    - [mysql](#mysql-38)
    - [pgsql](#pgsql-38)
  - [Delete+In(Array) 2](#deleteinarray-2)
    - [mysql](#mysql-39)
    - [pgsql](#pgsql-39)
  - [Update](#update)
    - [mysql](#mysql-40)
    - [pgsql](#pgsql-40)
  - [Update+In(Array)](#updateinarray)
    - [mysql](#mysql-41)
    - [pgsql](#pgsql-41)
  - [Update+In(Select)](#updateinselect)
    - [mysql](#mysql-42)
    - [pgsql](#pgsql-42)
  - [Select+Pagination](#selectpagination)
    - [mysql](#mysql-43)
    - [pgsql](#pgsql-43)
  - [Select+Where+When](#selectwherewhen)
    - [mysql](#mysql-44)
    - [pgsql](#pgsql-44)

---
### Demo
```php
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
```
#### mysql
```sql
SELECT
    `USER_1`.`id`,
    `USER_1`.`name`,
    SUM(`ARTICLE_1`.`rate`) AS `rate`
FROM
    `user` `USER_1`
LEFT JOIN `article` `ARTICLE_1` ON
    `ARTICLE_1`.`userId` = `USER_1`.`id`
WHERE
    (
        `USER_1`.`id` > 1 AND(`USER_1`.`id`, `USER_1`.`name`) IN(
        SELECT
            `USERINFO_1`.`id`,
            `USERINFO_1`.`description`
        FROM
            `userinfo` `USERINFO_1`
    )
    )
GROUP BY
    `USER_1`.`id`,
    `USER_1`.`name`
HAVING
    SUM(`ARTICLE_1`.`rate`) > 100
ORDER BY
    `USER_1`.`name` ASC
LIMIT 2
```
#### pgsql
```sql
SELECT
    "USER_1"."id",
    "USER_1"."name",
    SUM("ARTICLE_1"."rate") AS "rate"
FROM
    "user" "USER_1"
LEFT JOIN "article" "ARTICLE_1" ON
    "ARTICLE_1"."userId" = "USER_1"."id"
WHERE
    (
        "USER_1"."id" > 1 AND("USER_1"."id", "USER_1"."name") IN(
        SELECT
            "USERINFO_1"."id",
            "USERINFO_1"."description"
        FROM
            "userinfo" "USERINFO_1"
    )
    )
GROUP BY
    "USER_1"."id",
    "USER_1"."name"
HAVING
    SUM("ARTICLE_1"."rate") > 100
ORDER BY
    "USER_1"."name" ASC
LIMIT 2
```


---
### Select
```php
$builder->select(User::class)->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`id`,
    `USER_1`.`name`,
    `USER_1`.`age`,
    `USER_1`.`role`
FROM
    `user` `USER_1`
```
#### pgsql
```sql
SELECT
    "USER_1"."id",
    "USER_1"."name",
    "USER_1"."age",
    "USER_1"."role"
FROM
    "user" "USER_1"
```


---
### Select(Alias Column)
```php
$builder
    ->select(User::class, [
        'id',
        'name' => 'asName',
        'age as asAge',
        'role As asRole'
    ])
    ->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`id`,
    `USER_1`.`name` AS `asName`,
    `USER_1`.`age` AS `asAge`,
    `USER_1`.`role` AS `asRole`
FROM
    `user` `USER_1`
```
#### pgsql
```sql
SELECT
    "USER_1"."id",
    "USER_1"."name" AS "asName",
    "USER_1"."age" AS "asAge",
    "USER_1"."role" AS "asRole"
FROM
    "user" "USER_1"
```


---
### Select+Where+Like
```php
$builder
    ->select(User::class, ['name'])
    ->where(function (Where $where): void {
        $where
            ->like('name', 'I%');
    })
    ->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`name` LIKE 'I%'
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."name" LIKE 'I%'
```


---
### Select+Where+Not Like
```php
$builder
    ->select(User::class, ['name'])
    ->where(function (Where $where): void {
        $where
            ->notLike('name', 'I%');
    })
    ->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`name` NOT LIKE 'I%'
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."name" NOT LIKE 'I%'
```


---
### Select+Where+Where 1
```php
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
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    (
        `USER_1`.`id` = 4 OR `USER_1`.`id` = 5 OR(
            `USER_1`.`id` > 3 AND `USER_1`.`id` < 6
        )
    )
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    (
        "USER_1"."id" = 4 OR "USER_1"."id" = 5 OR(
            "USER_1"."id" > 3 AND "USER_1"."id" < 6
        )
    )
```


---
### Select+Where+Where 2
```php
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
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    (
        `USER_1`.`id` = 4 AND `USER_1`.`id` = 5 AND(`USER_1`.`id` > 3 OR `USER_1`.`id` < 6)
    )
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    (
        "USER_1"."id" = 4 AND "USER_1"."id" = 5 AND("USER_1"."id" > 3 OR "USER_1"."id" < 6)
    )
```


---
### Select+Where+=Null
```php
$builder
    ->select(User::class, ['name'])
    ->where(function (Where $where): void {
        $where
            ->or()
            ->cond('id', '=', null);
    })
    ->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` = NULL
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" = NULL
```


---
### Select+Where+Null
```php
$builder
    ->select(User::class, ['name'])
    ->where(function (Where $where): void {
        $where
            ->or()
            ->cond('id', '=', 4)
            ->isNull('name');
    })
    ->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    (
        `USER_1`.`id` = 4 OR `USER_1`.`name` IS NULL
    )
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    (
        "USER_1"."id" = 4 OR "USER_1"."name" IS NULL
    )
```


---
### Select+Where+NotNull
```php
$builder
    ->select(User::class, ['name'])
    ->where(function (Where $where): void {
        $where
            ->or()
            ->cond('id', '=', 4)
            ->isNotNull('name');
    })
    ->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    (
        `USER_1`.`id` = 4 OR `USER_1`.`name` IS NOT NULL
    )
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    (
        "USER_1"."id" = 4 OR "USER_1"."name" IS NOT NULL
    )
```


---
### Select+Where+Between
```php
$builder
    ->select(Article::class, ['id'])
    ->where(function (Where $where): void {
        $where
            ->between('id', 2, 7);
    })
    ->get();
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`id`
FROM
    `article` `ARTICLE_1`
WHERE
    `ARTICLE_1`.`id` BETWEEN 2 AND 7
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."id"
FROM
    "article" "ARTICLE_1"
WHERE
    "ARTICLE_1"."id" BETWEEN 2 AND 7
```


---
### Select+Where+Not Between
```php
$builder
    ->select(Article::class, ['id'])
    ->where(function (Where $where): void {
        $where
            ->notBetween('id', 2, 7);
    })
    ->get();
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`id`
FROM
    `article` `ARTICLE_1`
WHERE
    `ARTICLE_1`.`id` BETWEEN 2 AND 7
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."id"
FROM
    "article" "ARTICLE_1"
WHERE
    "ARTICLE_1"."id" BETWEEN 2 AND 7
```


---
### Select+Join+Inner
```php
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
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`title`,
    `USER_1`.`name`
FROM
    `article` `ARTICLE_1`
INNER JOIN `user` `USER_1` ON
    `USER_1`.`id` = `ARTICLE_1`.`userId`
WHERE
    `USER_1`.`id` > 3
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."title",
    "USER_1"."name"
FROM
    "article" "ARTICLE_1"
INNER JOIN "user" "USER_1" ON
    "USER_1"."id" = "ARTICLE_1"."userId"
WHERE
    "USER_1"."id" > 3
```


---
### Select+Join+Left
```php
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
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`title`,
    `USER_1`.`name`
FROM
    `article` `ARTICLE_1`
LEFT JOIN `user` `USER_1` ON
    `USER_1`.`id` = `ARTICLE_1`.`userId`
WHERE
    `USER_1`.`id` > 3
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."title",
    "USER_1"."name"
FROM
    "article" "ARTICLE_1"
LEFT JOIN "user" "USER_1" ON
    "USER_1"."id" = "ARTICLE_1"."userId"
WHERE
    "USER_1"."id" > 3
```


---
### Select+Join+Right
```php
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
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`title`,
    `USER_1`.`name`
FROM
    `article` `ARTICLE_1`
RIGHT JOIN `user` `USER_1` ON
    `USER_1`.`id` = `ARTICLE_1`.`userId`
WHERE
    `USER_1`.`id` > 3
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."title",
    "USER_1"."name"
FROM
    "article" "ARTICLE_1"
RIGHT JOIN "user" "USER_1" ON
    "USER_1"."id" = "ARTICLE_1"."userId"
WHERE
    "USER_1"."id" > 3
```


---
### Select+Join(3)
```php
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
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`title`,
    `USER_1`.`name`,
    `USER_2`.`name` AS `name2`,
    `USERINFO_1`.`id`,
    `USERINFO_1`.`description`,
    `USERINFO_1`.`userId`
FROM
    `article` `ARTICLE_1`
LEFT JOIN `user` `USER_1` ON
    `USER_1`.`id` = `ARTICLE_1`.`userId`
LEFT JOIN `user` `USER_2` ON
    `USER_2`.`id` = `USER_1`.`id`
LEFT JOIN `userinfo` `USERINFO_1` ON
    `USERINFO_1`.`id` = `USER_1`.`id`
WHERE
    (
        `ARTICLE_1`.`id` = 4 AND `ARTICLE_1`.`id` = 5 AND `USER_1`.`id` > 3 AND `USER_2`.`id` != 33 AND `USERINFO_1`.`id` != 7
    )
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."title",
    "USER_1"."name",
    "USER_2"."name" AS "name2",
    "USERINFO_1"."id",
    "USERINFO_1"."description",
    "USERINFO_1"."userId"
FROM
    "article" "ARTICLE_1"
LEFT JOIN "user" "USER_1" ON
    "USER_1"."id" = "ARTICLE_1"."userId"
LEFT JOIN "user" "USER_2" ON
    "USER_2"."id" = "USER_1"."id"
LEFT JOIN "userinfo" "USERINFO_1" ON
    "USERINFO_1"."id" = "USER_1"."id"
WHERE
    (
        "ARTICLE_1"."id" = 4 AND "ARTICLE_1"."id" = 5 AND "USER_1"."id" > 3 AND "USER_2"."id" != 33 AND "USERINFO_1"."id" != 7
    )
```


---
### Select+Join+OrderBy 1
```php
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
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`title`,
    `USER_1`.`name`
FROM
    `article` `ARTICLE_1`
LEFT JOIN `user` `USER_1` ON
    `USER_1`.`id` = `ARTICLE_1`.`userId`
WHERE
    (
        `ARTICLE_1`.`id` = 4 AND `ARTICLE_1`.`id` = 5 AND `USER_1`.`id` > 3
    )
ORDER BY
    `USER_1`.`id` ASC
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."title",
    "USER_1"."name"
FROM
    "article" "ARTICLE_1"
LEFT JOIN "user" "USER_1" ON
    "USER_1"."id" = "ARTICLE_1"."userId"
WHERE
    (
        "ARTICLE_1"."id" = 4 AND "ARTICLE_1"."id" = 5 AND "USER_1"."id" > 3
    )
ORDER BY
    "USER_1"."id" ASC
```


---
### Select+Join+OrderBy 2
```php
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
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`title`,
    `USER_1`.`name`
FROM
    `article` `ARTICLE_1`
LEFT JOIN `user` `USER_1` ON
    `USER_1`.`id` = `ARTICLE_1`.`userId`
WHERE
    (
        `ARTICLE_1`.`id` = 4 AND `ARTICLE_1`.`id` = 5 AND(
            `ARTICLE_1`.`id` = 7 AND `USER_1`.`id` = `ARTICLE_1`.`id`
        )
    )
ORDER BY
    `USER_1`.`id` ASC
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."title",
    "USER_1"."name"
FROM
    "article" "ARTICLE_1"
LEFT JOIN "user" "USER_1" ON
    "USER_1"."id" = "ARTICLE_1"."userId"
WHERE
    (
        "ARTICLE_1"."id" = 4 AND "ARTICLE_1"."id" = 5 AND(
            "ARTICLE_1"."id" = 7 AND "USER_1"."id" = "ARTICLE_1"."id"
        )
    )
ORDER BY
    "USER_1"."id" ASC
```


---
### Select+Join
```php
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
```
#### mysql
```sql
SELECT
    `COMMENT_1`.`id`,
    `COMMENT_1`.`createAt`,
    `ARTICLE_1`.`title`,
    `USER_1`.`name` AS `authorArticleName`,
    `USER_2`.`name` AS `authorCommentName`
FROM
    `comment` `COMMENT_1`
LEFT JOIN `article` `ARTICLE_1` ON
    `ARTICLE_1`.`id` = `COMMENT_1`.`articleId`
LEFT JOIN `user` `USER_1` ON
    `USER_1`.`id` = `COMMENT_1`.`userId`
LEFT JOIN `user` `USER_2` ON
    `USER_2`.`id` = `ARTICLE_1`.`userId`
WHERE
    `COMMENT_1`.`id` IN(1, 3, 5)
```
#### pgsql
```sql
SELECT
    "COMMENT_1"."id",
    "COMMENT_1"."createAt",
    "ARTICLE_1"."title",
    "USER_1"."name" AS "authorArticleName",
    "USER_2"."name" AS "authorCommentName"
FROM
    "comment" "COMMENT_1"
LEFT JOIN "article" "ARTICLE_1" ON
    "ARTICLE_1"."id" = "COMMENT_1"."articleId"
LEFT JOIN "user" "USER_1" ON
    "USER_1"."id" = "COMMENT_1"."userId"
LEFT JOIN "user" "USER_2" ON
    "USER_2"."id" = "ARTICLE_1"."userId"
WHERE
    "COMMENT_1"."id" IN(1, 3, 5)
```


---
### Select+Relation
```php
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
```
#### mysql
```sql
SELECT
    `COMMENT_1`.`id`,
    `COMMENT_1`.`createAt`,
    `ARTICLE_1`.`title`,
    `USER_1`.`name` AS `authorArticleName`,
    `USER_2`.`name` AS `authorCommentName`
FROM
    `comment` `COMMENT_1`
LEFT JOIN `article` `ARTICLE_1` ON
    `ARTICLE_1`.`id` = `COMMENT_1`.`articleId`
LEFT JOIN `user` `USER_1` ON
    `USER_1`.`id` = `ARTICLE_1`.`userId`
LEFT JOIN `user` `USER_2` ON
    `USER_2`.`id` = `COMMENT_1`.`userId`
WHERE
    `COMMENT_1`.`id` IN(1, 3, 5)
```
#### pgsql
```sql
SELECT
    "COMMENT_1"."id",
    "COMMENT_1"."createAt",
    "ARTICLE_1"."title",
    "USER_1"."name" AS "authorArticleName",
    "USER_2"."name" AS "authorCommentName"
FROM
    "comment" "COMMENT_1"
LEFT JOIN "article" "ARTICLE_1" ON
    "ARTICLE_1"."id" = "COMMENT_1"."articleId"
LEFT JOIN "user" "USER_1" ON
    "USER_1"."id" = "ARTICLE_1"."userId"
LEFT JOIN "user" "USER_2" ON
    "USER_2"."id" = "COMMENT_1"."userId"
WHERE
    "COMMENT_1"."id" IN(1, 3, 5)
```


---
### Select+Sum
```php
$builder
    ->select(User::class, ['sum(age)'])
    ->where(function (Where $where): void {
        $where->or()->cond('id', '=', 4)->cond('id', '=', 5);
    })
    ->get();
```
#### mysql
```sql
SELECT
    SUM(`USER_1`.`age`) AS `sum(age)`
FROM
    `user` `USER_1`
WHERE
    (`USER_1`.`id` = 4 OR `USER_1`.`id` = 5)
```
#### pgsql
```sql
SELECT
    SUM("USER_1"."age") AS "sum(age)"
FROM
    "user" "USER_1"
WHERE
    ("USER_1"."id" = 4 OR "USER_1"."id" = 5)
```


---
### Select+Sum(Alias Column)
```php
$builder
    ->select(User::class, [
        'sum(age) as sumAge',
        'avg(age)' => 'avgAge'
    ])
    ->where(function (Where $where): void {
        $where->or()->cond('id', '=', 4)->cond('id', '=', 5);
    })
    ->get();
```
#### mysql
```sql
SELECT
    SUM(`USER_1`.`age`) AS `sumAge`,
    AVG(`USER_1`.`age`) AS `avgAge`
FROM
    `user` `USER_1`
WHERE
    (`USER_1`.`id` = 4 OR `USER_1`.`id` = 5)
```
#### pgsql
```sql
SELECT
    SUM("USER_1"."age") AS "sumAge",
    AVG("USER_1"."age") AS "avgAge"
FROM
    "user" "USER_1"
WHERE
    ("USER_1"."id" = 4 OR "USER_1"."id" = 5)
```


---
### Select+Limit 1
```php
$builder
    ->select(User::class, ['id', 'name'])
    ->limit(10, 5)
    ->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`id`,
    `USER_1`.`name`
FROM
    `user` `USER_1`
LIMIT 10 OFFSET 5
```
#### pgsql
```sql
SELECT
    "USER_1"."id",
    "USER_1"."name"
FROM
    "user" "USER_1"
LIMIT 10 OFFSET 5
```


---
### Select+Limit 2
```php
$builder
    ->select(User::class, ['id', 'name'])
    ->limit(9)
    ->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`id`,
    `USER_1`.`name`
FROM
    `user` `USER_1`
LIMIT 9
```
#### pgsql
```sql
SELECT
    "USER_1"."id",
    "USER_1"."name"
FROM
    "user" "USER_1"
LIMIT 9
```


---
### Select+Distinct
```php
$builder
    ->select(Article::class, ['userId'])
    ->Distinct()
    ->get();
```
#### mysql
```sql
SELECT DISTINCT
    `ARTICLE_1`.`userId`
FROM
    `article` `ARTICLE_1`
```
#### pgsql
```sql
SELECT DISTINCT
    "ARTICLE_1"."userId"
FROM
    "article" "ARTICLE_1"
```


---
### Select+OrderBy
```php
$builder
    ->select(User::class, ['id', 'name'])
    ->OrderBy('id', false)
    ->OrderBy('name', true)
    ->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`id`,
    `USER_1`.`name`
FROM
    `user` `USER_1`
ORDER BY
    `USER_1`.`id`
DESC
    ,
    `USER_1`.`name` ASC
```
#### pgsql
```sql
SELECT
    "USER_1"."id",
    "USER_1"."name"
FROM
    "user" "USER_1"
ORDER BY
    "USER_1"."id"
DESC
    ,
    "USER_1"."name" ASC
```


---
### Select+GroupBy
```php
$builder
    ->select(Article::class, ['userId'])
    ->GroupBy('userId')
    ->get();
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`userId`
FROM
    `article` `ARTICLE_1`
GROUP BY
    `ARTICLE_1`.`userId`
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."userId"
FROM
    "article" "ARTICLE_1"
GROUP BY
    "ARTICLE_1"."userId"
```


---
### Select+Having
```php
$builder
    ->select(Article::class, ['userId', 'count(userId)' => 'cnt'])
    ->GroupBy('userId')
    ->having('count(userId)', '>', 1)
    ->get();
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`userId`,
    COUNT(`ARTICLE_1`.`userId`) AS `cnt`
FROM
    `article` `ARTICLE_1`
GROUP BY
    `ARTICLE_1`.`userId`
HAVING
    COUNT(`ARTICLE_1`.`userId`) > 1
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."userId",
    COUNT("ARTICLE_1"."userId") AS "cnt"
FROM
    "article" "ARTICLE_1"
GROUP BY
    "ARTICLE_1"."userId"
HAVING
    COUNT("ARTICLE_1"."userId") > 1
```


---
### Select+Having+Between
```php
$builder
    ->select(Article::class, ['userId', 'count(userId)' => 'cnt'])
    ->GroupBy('userId')
    ->having('count(userId)', 'between', [2, 5])
    ->get();
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`userId`,
    COUNT(`ARTICLE_1`.`userId`) AS `cnt`
FROM
    `article` `ARTICLE_1`
GROUP BY
    `ARTICLE_1`.`userId`
HAVING
    COUNT(`ARTICLE_1`.`userId`) BETWEEN 2 AND 5
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."userId",
    COUNT("ARTICLE_1"."userId") AS "cnt"
FROM
    "article" "ARTICLE_1"
GROUP BY
    "ARTICLE_1"."userId"
HAVING
    COUNT("ARTICLE_1"."userId") BETWEEN 2 AND 5
```


---
### Select+Having+Not Between
```php
$builder
    ->select(Article::class, ['userId', 'count(userId)' => 'cnt'])
    ->GroupBy('userId')
    ->having('count(userId)', '   not    between  ', [2, 5])
    ->get();
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`userId`,
    COUNT(`ARTICLE_1`.`userId`) AS `cnt`
FROM
    `article` `ARTICLE_1`
GROUP BY
    `ARTICLE_1`.`userId`
HAVING
    COUNT(`ARTICLE_1`.`userId`) NOT BETWEEN 2 AND 5
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."userId",
    COUNT("ARTICLE_1"."userId") AS "cnt"
FROM
    "article" "ARTICLE_1"
GROUP BY
    "ARTICLE_1"."userId"
HAVING
    COUNT("ARTICLE_1"."userId") NOT BETWEEN 2 AND 5
```


---
### Select+In(Array)
```php
$builder
    ->select(User::class, ['name'])
    ->where(function (Where $where) {
        $where->inArray('id', [3, 4]);
    })
    ->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` IN(3, 4)
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" IN(3, 4)
```


---
### Select+In(Select)
```php
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
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` IN(
    SELECT
        `ARTICLE_1`.`userId`
    FROM
        `article` `ARTICLE_1`
    WHERE
        `ARTICLE_1`.`id` = 3
)
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" IN(
    SELECT
        "ARTICLE_1"."userId"
    FROM
        "article" "ARTICLE_1"
    WHERE
        "ARTICLE_1"."id" = 3
)
```


---
### Select+In(Select)+MultiColumns
```php
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
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    (`USER_1`.`id`, `USER_1`.`name`) IN(
    SELECT
        `ARTICLE_1`.`userId`,
        `ARTICLE_1`.`title`
    FROM
        `article` `ARTICLE_1`
    WHERE
        `ARTICLE_1`.`id` = 2
)
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    ("USER_1"."id", "USER_1"."name") IN(
    SELECT
        "ARTICLE_1"."userId",
        "ARTICLE_1"."title"
    FROM
        "article" "ARTICLE_1"
    WHERE
        "ARTICLE_1"."id" = 2
)
```


---
### Select+In(Select)+Function
```php
$builder
    ->select(Article::class, ['id', 'userId'])
    ->where(function (Where $where) {
        $where->inSelect('userId', Article::class, 'min(userId)');
    })
    ->get();
```
#### mysql
```sql
SELECT
    `ARTICLE_1`.`id`,
    `ARTICLE_1`.`userId`
FROM
    `article` `ARTICLE_1`
WHERE
    `ARTICLE_1`.`userId` IN(
    SELECT
        MIN(`ARTICLE_2`.`userId`) AS `min(userId)`
    FROM
        `article` `ARTICLE_2`
)
```
#### pgsql
```sql
SELECT
    "ARTICLE_1"."id",
    "ARTICLE_1"."userId"
FROM
    "article" "ARTICLE_1"
WHERE
    "ARTICLE_1"."userId" IN(
    SELECT
        MIN("ARTICLE_2"."userId") AS "min(userId)"
    FROM
        "article" "ARTICLE_2"
)
```


---
### Select+In(Select)+Context.Name
```php
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
```
#### mysql
```sql
SELECT
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` IN(
    SELECT
        `ARTICLE_1`.`userId`
    FROM
        `article` `ARTICLE_1`
    WHERE
        `ARTICLE_1`.`id` = `USER_1`.`id`
)
```
#### pgsql
```sql
SELECT
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" IN(
    SELECT
        "ARTICLE_1"."userId"
    FROM
        "article" "ARTICLE_1"
    WHERE
        "ARTICLE_1"."id" = "USER_1"."id"
)
```


---
### Insert+Values(Array)
```php
$builder
    ->insert(User::class)
    ->values([
        'name' => 'Igor',
        'age' => ColumnInteger::random(18, 88),
        'role' => 'Moderator'
    ])
    ->exec();
```
#### mysql
```sql
INSERT INTO `user`(`name`, `age`, `role`)
VALUES('Igor', 26, 'Moderator')
```
#### pgsql
```sql
INSERT INTO "user"("name", "age", "role")
VALUES('Igor', 40, 'Moderator')
```


---
### Insert+Values(Closure)
```php
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
```
#### mysql
```sql
INSERT INTO `user`(`name`, `age`, `role`)
VALUES(
    'Alex',
    (
    SELECT
        `USER_2`.`id`
    FROM
        `user` `USER_2`
    WHERE
        `USER_2`.`id` = 8
),
'Vip'
)
```
#### pgsql
```sql
INSERT INTO "user"("name", "age", "role")
VALUES(
    'Alex',
    (
    SELECT
        "USER_2"."id"
    FROM
        "user" "USER_2"
    WHERE
        "USER_2"."id" = 8
),
'Vip'
)
```


---
### Delete
```php
$builder
    ->delete(User::class)
    ->where(function (Where $where) {
        $where->cond('id', '>', 3);
    })
    ->exec();
```
#### mysql
```sql
SELECT
    `USER_1`.`id`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` > 3
DELETE
    `USER_1`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` > 3
```
#### pgsql
```sql
SELECT
    "USER_1"."id"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" > 3
DELETE
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" > 3
```


---
### Delete+In(Array) 1
```php
$builder
    ->delete(User::class)
    ->where(function (Where $where) {
        $where->inArray('id', [3, 4]);
    })
    ->exec();
```
#### mysql
```sql
SELECT
    `USER_1`.`id`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` IN(3, 4)
DELETE
    `USER_1`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` IN(3, 4)
```
#### pgsql
```sql
SELECT
    "USER_1"."id"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" IN(3, 4)
DELETE
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" IN(3, 4)
```


---
### Delete+In(Array) 2
```php
$builder
    ->delete(User::class)
    ->where(function (Where $where) {
        $where->inArray('id', [5]);
    })
    ->exec();
```
#### mysql
```sql
SELECT
    `USER_1`.`id`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` = 5
DELETE
    `USER_1`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` = 5
```
#### pgsql
```sql
SELECT
    "USER_1"."id"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" = 5
DELETE
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" = 5
```


---
### Update
```php
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
```
#### mysql
```sql
UPDATE
    `user` `USER_1`
SET
    `name` = 'Admin',
    `age` = 7
WHERE
    `USER_1`.`id` = 3
SELECT
    `USER_1`.`id`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` = 3
```
#### pgsql
```sql
UPDATE
    "user" "USER_1"
SET
    "name" = 'Admin',
    "age" = 7
WHERE
    "USER_1"."id" = 3
SELECT
    "USER_1"."id"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" = 3
```


---
### Update+In(Array)
```php
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
```
#### mysql
```sql
UPDATE
    `user` `USER_1`
SET
    `name` = 'Admin',
    `age` = 7
WHERE
    `USER_1`.`id` IN(1, 2, 3)
SELECT
    `USER_1`.`id`
FROM
    `user` `USER_1`
WHERE
    `USER_1`.`id` IN(1, 2, 3)
```
#### pgsql
```sql
UPDATE
    "user" "USER_1"
SET
    "name" = 'Admin',
    "age" = 7
WHERE
    "USER_1"."id" IN(1, 2, 3)
SELECT
    "USER_1"."id"
FROM
    "user" "USER_1"
WHERE
    "USER_1"."id" IN(1, 2, 3)
```


---
### Update+In(Select)
```php
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
```
#### mysql
```sql
UPDATE
    `article` `ARTICLE_1`
SET
    `title` =(
    SELECT
        `USER_1`.`name`
    FROM
        `user` `USER_1`
    LIMIT 1
)
WHERE
    `ARTICLE_1`.`id` IN(1, 2, 3)
SELECT
    `ARTICLE_1`.`id`
FROM
    `article` `ARTICLE_1`
WHERE
    `ARTICLE_1`.`id` IN(1, 2, 3)
```
#### pgsql
```sql
UPDATE
    "article" "ARTICLE_1"
SET
    "title" =(
    SELECT
        "USER_1"."name"
    FROM
        "user" "USER_1"
    LIMIT 1
)
WHERE
    "ARTICLE_1"."id" IN(1, 2, 3)
SELECT
    "ARTICLE_1"."id"
FROM
    "article" "ARTICLE_1"
WHERE
    "ARTICLE_1"."id" IN(1, 2, 3)
```


---
### Select+Pagination
```php
$builder->select(User::class, ['id', 'name'])->pagination(2, 3);
```
#### mysql
```sql
SELECT
    COUNT(*) AS `cnt`
FROM
    `user` `USER_1`
SELECT
    `USER_1`.`id`,
    `USER_1`.`name`
FROM
    `user` `USER_1`
LIMIT 2 OFFSET 6
```
#### pgsql
```sql
SELECT
    COUNT(*) AS "cnt"
FROM
    "user" "USER_1"
SELECT
    "USER_1"."id",
    "USER_1"."name"
FROM
    "user" "USER_1"
LIMIT 2 OFFSET 6
```


---
### Select+Where+When
```php
$builder->select(User::class, ['id', 'name'])->where(function (Where $where) {
    for ($id = 1; $id <= 8; $id++) {
        // Фильтрация по условию
        $where->when($id % 2 == 1, function (Where $where) use ($id) {
            $where->cond('id', '=', $id);
        });
    }
})->get();
```
#### mysql
```sql
SELECT
    `USER_1`.`id`,
    `USER_1`.`name`
FROM
    `user` `USER_1`
WHERE
    (
        `USER_1`.`id` = 1 AND `USER_1`.`id` = 3 AND `USER_1`.`id` = 5 AND `USER_1`.`id` = 7
    )
```
#### pgsql
```sql
SELECT
    "USER_1"."id",
    "USER_1"."name"
FROM
    "user" "USER_1"
WHERE
    (
        "USER_1"."id" = 1 AND "USER_1"."id" = 3 AND "USER_1"."id" = 5 AND "USER_1"."id" = 7
    )
```


