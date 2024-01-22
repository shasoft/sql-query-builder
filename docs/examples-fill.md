## Sql Query Builder fill examples
* [FillSimple](#fillsimple)
* [FillSimple+Relation](#fillsimplerelation)
* [Fill+Null](#fillnull)
* [FillSimple+NoCache](#fillsimplenocache)
* [Fill+NoCache](#fillnocache)
* [Fill 1](#fill-1)
* [Fill 2](#fill-2)

---
### FillSimple
```php
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
```
#### mysql
```sql
SELECT
    *
FROM
    `comment`
WHERE
    `id` IN(1, 3, 5)
SELECT
    *
FROM
    `article`
WHERE
    `id` IN(1, 8, 4)
SELECT
    *
FROM
    `user`
WHERE
    `id` IN(6, 3, 1, 5, 7)
```


---
### FillSimple+Relation
```php
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
```
#### mysql
```sql
```


---
### Fill+Null
```php
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
```
#### mysql
```sql
SELECT
    *
FROM
    `comment`
WHERE
    `id` IN(9, 13)
SELECT
    *
FROM
    `user`
WHERE
    `id` = 8
```


---
### FillSimple+NoCache
```php
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
```
#### mysql
```sql
SELECT
    `COMMENT_1`.`text`,
    `COMMENT_1`.`id` AS `~~~id`,
    `ARTICLE_1`.`title`,
    `USER_1`.`name` AS `authorCommentName`,
    `USER_2`.`name` AS `authorArticleName`
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


---
### Fill+NoCache
```php
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
```
#### mysql
```sql
SELECT
    `COMMENT_1`.`text`,
    `COMMENT_1`.`id` AS `~~~id`,
    `COMMENT_2`.`id` AS `parent`,
    `COMMENT_3`.`id` AS `root`,
    `USER_1`.`name` AS `authorNameRoot`,
    `ARTICLE_1`.`title`,
    `USER_2`.`name` AS `authorArticleName`,
    `USER_3`.`name` AS `authorCommentName`
FROM
    `comment` `COMMENT_1`
LEFT JOIN `comment` `COMMENT_2` ON
    `COMMENT_2`.`id` = `COMMENT_1`.`parentId`
LEFT JOIN `comment` `COMMENT_3` ON
    `COMMENT_3`.`id` = `COMMENT_2`.`parentId`
LEFT JOIN `user` `USER_1` ON
    `USER_1`.`id` = `COMMENT_3`.`userId`
LEFT JOIN `article` `ARTICLE_1` ON
    `ARTICLE_1`.`id` = `COMMENT_2`.`articleId`
LEFT JOIN `user` `USER_2` ON
    `USER_2`.`id` = `COMMENT_2`.`userId`
LEFT JOIN `user` `USER_3` ON
    `USER_3`.`id` = `ARTICLE_1`.`userId`
WHERE
    `COMMENT_1`.`id` IN(1, 3, 5)
```


---
### Fill 1
```php
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
```
#### mysql
```sql
```


---
### Fill 2
```php
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
```
#### mysql
```sql
SELECT
    *
FROM
    `comment`
WHERE
    `id` = 7
SELECT
    *
FROM
    `comment`
WHERE
    `id` = 2
SELECT
    *
FROM
    `article`
WHERE
    `id` = 10
SELECT
    *
FROM
    `comment`
WHERE
    `id` = 2
```


