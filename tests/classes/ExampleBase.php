<?php

namespace Shasoft\SqlQueryBuilder\Tests;

use Throwable;
use Shasoft\DbTool\DbToolPdo;
use Shasoft\DbTool\DbToolPdoLog;
use Shasoft\DbSchemaDev\Table\User;
use Shasoft\DbTool\DbToolSqlFormat;
use Shasoft\PsrCache\CacheItemPool;
use Shasoft\SqlQueryBuilder\Builder;
use Psr\Cache\CacheItemPoolInterface;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchemaDev\Table\Article;
use Shasoft\DbSchemaDev\Table\TabTest;
use Shasoft\DbSchema\DbSchemaExtraData;
use Shasoft\DbSchemaDev\Table\UserInfo;
use Shasoft\DbSchema\DbSchemaMigrations;
use Shasoft\DbSchemaDev\DbSchemaDevTool;
use Shasoft\SqlQueryBuilder\Command\Join;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\SqlQueryBuilder\Command\Where;
use Shasoft\SqlQueryBuilder\Command\Select;
use Shasoft\SqlQueryBuilder\Command\Values;
use Shasoft\SqlQueryBuilder\Tests\Unit\Base;
use Shasoft\SqlQueryBuilder\BuilderExtraData;
use Shasoft\SqlQueryBuilder\Command\JoinItem;
use Shasoft\SqlQueryBuilder\Command\Relation;
use Shasoft\PsrCache\Adapter\CacheAdapterArray;
use Shasoft\DbSchema\Driver\DbSchemaDriverMySql;
use Shasoft\SqlQueryBuilder\Tests\Table\Comment;
use Shasoft\DbSchema\Driver\DbSchemaDriverPostgreSql;
use Shasoft\SqlQueryBuilder\Tests\Table\TabNoPrimaryKey;
use Shasoft\SqlQueryBuilder\Exceptions\BuilderExceptionSqlError;

abstract class ExampleBase
{
    // Параметры PDO соединений
    static protected array $params = [
        // MySql
        'mysql' => [
            'dsn' => 'mysql:dbname=cmg-db-test;host=localhost',
            'username' => 'root',
            'password' => null,
            'classDriver' => DbSchemaDriverMySql::class
        ],
        // PostgreSql
        'pgsql' => [
            'dsn' => 'pgsql:dbname=cmg-db-test;host=localhost',
            'username' => 'postgres',
            'password' => '123',
            'classDriver' => DbSchemaDriverPostgreSql::class
        ]
    ];
    // Короткий режим имени таблицы
    protected bool $shortTableName = false;
    // Конструктор
    public function __construct(protected array $pdoDriverNames, protected ?CacheItemPoolInterface $cache = null)
    {
    }
    // Установить короткий режим имени таблицы
    public function setShortTableName(bool $value): static
    {
        $this->shortTableName = $value;
        // Вернуть указатель на себя
        return $this;
    }
    // Создать построитель запросов
    static public function createBuilder(string $name, bool $hasSeeder, ?CacheItemPoolInterface $cache = null): Builder
    {
        // Параметры 
        $params = self::$params[$name];
        // Создать PDO соединение
        $pdo = new \PDO(
            $params['dsn'],
            $params['username'],
            $params['password']
        );
        // Удалить всё из БД
        DbToolPdo::reset($pdo);
        // Получить миграции
        $migrations = DbSchemaMigrations::get(
            [
                //TabTest::class,
                User::class,
                UserInfo::class,
                Article::class,
                Comment::class,
                TabNoPrimaryKey::class
            ],
            $params['classDriver']
        );
        // Выполнить дополнительный расчет
        BuilderExtraData::run($migrations);
        // Определим поля первичного индекса
        //s_dd($migrations);

        // Выполнить миграции
        $migrations->run($pdo);
        // Сгенерировать данные
        if ($hasSeeder) {
            DbSchemaDevTool::seeder($pdo, $migrations->database(), 10, 0);
        }
        // Создать построитель запросов
        return new Builder($pdo, $migrations->database(), $cache);
    }
    // Преобразовать имя таблицы
    protected function replaceTableName(string $sql, ?bool $shortTableName = null): string
    {
        if (is_null($shortTableName)) {
            $shortTableName = $this->shortTableName;
        }
        if ($shortTableName) {
            $sql = str_replace('shasoft-dbschemadev-table-', '', $sql);
            $sql = str_replace('shasoft-sqlquerybuilder-tests-table-', '', $sql);
        }
        return $sql;
    }
    // Получить текст выполненных текстов
    public function html(): string
    {
        //
        $top = '';
        $html = '';
        // Выполнить тесты
        $this->run(
            function (string $title, string $code, array $results) use (&$html, &$top) {
                $href = self::hrefName($title);
                $top .= '<li><a href="#' . $href . '">' . $title . '</a></li>';
                $html .= '<div style="padding:4px;background-color:DarkKhaki"><a name="' . $href . '"></a><strong style="color:Purple">' . $title . '</strong>';
                // Раскрасить PHP код
                $code = highlight_string("<?php\n" . $code, true);
                $code = str_replace("&lt;?php<br />", '', $code);
                //
                $html .= "<div style='border:dotted 1px SteelBlue;background-color:white;padding:4px'>" . $code . "</div>";
                foreach ($results as $name => $result) {
                    if ($result['err']) {
                        $color1 = 'Red';
                        $color2 = 'DarkRed';
                        // Выводить только информацию об ошибке
                        $html = '';
                    } else {
                        $color1 = 'Green';
                        $color2 = 'DarkGreen';
                    }
                    $html .= '<div style="padding:2px;background-color:Moccasin;border:solid 1px ' . $color1 . '">';
                    $html .= '<div style="padding:2px;border:solid 1px SteelBlue;font-weight: bold;color:LightSalmon;background-color:' . $color2 . '">' . $name . '</div>';
                    if (!empty($result['htmlDump'])) {
                        $html .= "<div>" . $result['htmlDump'] . "</div>";
                    }
                    if ($result['err']) {
                        $html .= '<div style="padding:2px;border:solid 1px SteelBlue;font-weight: bold;color:red;background-color:LightYellow">' . $result['message'] . '</div>';
                        $html .= "<div>" . $result['html'] . "</div>";
                    }
                    $html .= $this->replaceTableName($result['logRaw']);
                    $html .= '</div>';
                    // Если ошибка, то прервать выполнение
                    if ($result['err']) return false;
                }
                $html .= '</div><hr/>';
            }
        );
        return
            '<h3>Sql Query Builder examples</h3>' .
            '<h4>Справка</h4>' .
            '<P>Можно перед именем теста указывать спецсимволы:</P>' .
            '<ul>' .
            '<li><strong style="color:blue">+</strong> - выполнить только указанный тест</li>' .
            '<li><strong style="color:blue">*</strong> - выполнить только указанный тест только для одной PDO</li>' .
            '</ul>' .
            '<h4>Оглавление</h4>' .
            '<ul>' . $top . '</ul>' . $html;
    }
    // Получить страницу примеров
    public function markdown(string $title): string
    {
        //
        $top = '';
        $html = '';
        // Выполнить тесты
        $this->run(
            function (string $title, string $code, array $results) use (&$html, &$top) {
                $href = self::hrefName($title);
                $top .= '* [' . $title . '](#' . $href . ')' . "\n";
                $html .= '---' . "\n";
                $html .= '### ' . $title . "\n";
                $html .= "```php\n" . $code . "\n```\n";
                foreach ($results as $name => $result) {
                    $html .= '#### ' . $name . "\n";
                    $html .= "```sql\n";
                    foreach ($result['raw'] as $sql) {
                        $sql = $this->replaceTableName($sql, true);
                        $html .= trim(DbToolSqlFormat::console($sql)) . "\n";
                    }
                    $html .= "```\n";
                }
                $html .= "\n\n";
            }
        );
        return
            '## ' . $title . "\n" .
            $top . "\n" . $html;
    }
    // Получить имя ссылки
    public function hrefName(string $title): string
    {
        return strtolower(
            str_replace(
                ' ',
                '-',
                preg_replace('/[^a-zA-Z0-9 ]/ui', '', $title)
            )
        );
    }
    // Выполнить все примеры
    public function run(\Closure $cb): Builder
    {
        // Создать БД и построитель запроса для каждого PDO
        $builders = [];
        foreach ($this->pdoDriverNames as $pdoDriverName) {
            // Создать построитель запросов
            $builders[$pdoDriverName] = self::createBuilder($pdoDriverName, true, $this->cache);
        }
        // Может выполнять только по одной БД?
        $hasOnePdo = false;
        // Удалить примеры, которые исключены
        $examplesAll = array_filter($this->get(), function (string $key) {
            return (substr($key, 0, 1) != '~');
        }, ARRAY_FILTER_USE_KEY);
        // Может есть выделенные примеры?
        $examples = array_filter($examplesAll, function (string $key) {
            return (substr($key, 0, 1) == '+');
        }, ARRAY_FILTER_USE_KEY);
        if (empty($examples)) {
            //
            $examples = array_filter($examplesAll, function (string $key) {
                return (substr($key, 0, 1) == '*');
            }, ARRAY_FILTER_USE_KEY);
            if (empty($examples)) {
                $examples = $examplesAll;
            } else {
                $hasOnePdo = true;
            }
        }
        // Выполнить все примеры
        foreach ($examples as $title => $exampleFn) {
            if (!empty($title)) {
                //
                $refFn = new \ReflectionFunction($exampleFn);
                $lines = array_filter(explode("\n", file_get_contents($refFn->getFileName())), function (string $str, int $line) use ($refFn) {
                    $line++;
                    return !empty(trim($str)) && $line > $refFn->getStartLine() && $line < $refFn->getEndLine();
                }, ARRAY_FILTER_USE_BOTH);
                // Определить минимальное количество пробелов
                $minSpaces = 1000;
                foreach ($lines as $line) {
                    $minSpaces = min($minSpaces, strlen($line) - strlen(ltrim($line)));
                }
                // Обрезать пробелы
                foreach ($lines as &$line) {
                    $line = substr($line, $minSpaces);
                }
                $code = implode("\n", $lines);
                //
                $results = [];
                foreach ($builders as $pdoDriverName => $builder) {
                    // Выполнить тесты
                    $results[$pdoDriverName] = $this->runExample($builder, $exampleFn, $title);
                    // Если выполнять только по одной PDO?
                    if ($hasOnePdo) break;
                }
                // Вызвать функцию обработки
                $rc = $cb($title, $code, $results);
                //
                if ($rc === false) break;
            }
        }
        return $builder;
    }
    // Выполнить пример
    public function runExample(Builder $builder, \Closure $exampleFn, string $title): array
    {
        DbToolPdoLog::clear();
        //
        $errMessage = '';
        $errHtml = '';
        ob_start();
        try {
            // Выполнить тест
            $exampleFn($builder);
        } catch (\Throwable $t) {
            $errMessage = $t->getMessage();
            $errHtml = '<h3>' . $title . '</h3>';
            if ($t instanceof BuilderExceptionSqlError) {
                $errHtml .=
                    s_dump_html($t) .
                    DbToolSqlFormat::auto($t->getSql()) .
                    s_dump_html($t->getParams());
            } else {
                $errHtml .= s_dump_html($t);
            }
        }
        $htmlDump = ob_get_contents();
        ob_end_clean();
        $log = DbToolPdoLog::getLog();
        return [
            'err' => (!empty($errMessage)),
            'message' => $errMessage,
            'html' => $errHtml,
            'raw' => DbToolPdoLog::getRaw(),
            'log' => str_replace('shasoft-dbschemadev-table-', '', $log),
            'logRaw' => $log,
            'htmlDump' => $htmlDump
        ];
    }
    // Тесты
    abstract public function get(): array;
}
