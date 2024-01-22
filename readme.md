## Пакет построитель SQL запросов к БД

Особенности:
* Работа напрямую с драйвером PDO ([mysql](https://www.php.net/manual/ref.pdo-mysql.php) и [pgsql](https://www.php.net/manual/ref.pdo-pgsql.php))
* Поддержка SELECT, INSERT, UPDATE, DELETE
* Соединения JOIN (INNER, LEFT, RIGHT)
* Подзапросы в условиях фильтрации (в том числе многостолбцовые подзапросы)
* Исключение дубликатов, DISTINCT
* Сортировка, оператор ORDER BY
* Группировка, оператор GROUP BY
* Агрегатные функции
* Оператор HAVING
* Ограничение выборки, оператор LIMIT
* Поддержка пагинации (возвращается объект пагинации который содержит всю информацию (текущая страница, общее количество страниц и т.д.))
* Поддержка выборки данных с использованием КЭШирования

Результирующая выборка конвертирует выбранные данные в типы данных, определенные в миграциях через пакет версионирования [shasoft/db-schema](https://github.com/shasoft/db-schema).

```php
    // Создать построитель запросов
    $builder = new Builder(
        // PDO драйвер соединения с БД
        \PDO $pdo,
        // Состояние БД
        StateDatabase $stateDatabase
    );
    // Сгенерировать и выполнить запрос
    $builder->select(User::class, ['id', 'name'])
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
            $join->left(Article::class, 
                ['userId' => 'id'], 
                ['sum(rate) as rate'])
                ->having('SUM(rate)', '>', 100);
        })
        ->limit(2)->orderBy('name')
        ->groupBy('id')->groupBy('name')
        ->get();
```
Сгенерированный запрос
```sql
SELECT
    `U`.`id`,
    `U`.`name`,
    SUM(`B`.`rate`) AS `rate`
  FROM `user` `U`
  LEFT JOIN `article` `B` 
         ON `B`.`userId` = `U`.`id`
 WHERE (`U`.`id` > 1 AND(`U`.`id`, `U`.`name`) 
        IN( SELECT  `A`.`id`, `A`.`description` 
              FROM `userinfo` `A` ) )
 GROUP BY  `U`.`id`, `U`.`name`
HAVING SUM(`B`.`rate`) > 100
 ORDER BY `U`.`name` ASC
 LIMIT 2
```