<?php
include_once '../vendor/autoload.php';
error_reporting(E_ALL);

use nx\helpers\db\sql;
use nx\helpers\db\sql\value;
use nx\test\test;

// 测试 value 类型处理
test::case('测试字符串值', (string)new value('hello'))->toBe('"hello"');

test::case('测试数字值', (string)new value(123))->toBe('123');

test::case('测试布尔值 true', (string)new value(true))->toBe('TRUE');

test::case('测试布尔值 false', (string)new value(false))->toBe('FALSE');

test::case('测试 null 值', (string)new value(null))->toBe('NULL');

test::case('测试数组值', (string)new value([1, 2, 3]))->toBe('1,2,3');

test::case('测试星号值', (string)new value('*'))->toBe('*');

test::case('测试转义星号值', (string)new value('\*'))->toBe('"*"');

test::case('测试带别名的值', (string)(new value('test'))->as('alias'))->toBe('"test" `alias`');

// 测试 value 在 SQL 上下文中的行为
$sql = new sql(sql::table('test'));
$result = (string)new value('hello');

test::case('测试 SQL 上下文中字符串值', $result)->toBe('"hello"'); // 注意：这里会根据实际实现有所不同

// 测试带参数收集的值
sql::$current = new sql(sql::table('test'));
$value = new value('test');
$param = $value->__toString();
sql::$current = null;

test::case('测试 SQL 上下文中参数收集', $param)->toBe('?');

// 测试带别名的值
test::case('测试带别名的字符串值', (string)(new value('hello'))->as('alias'))->toBe('"hello" `alias`');

test::case('测试带别名的数字值', (string)(new value(123))->as('alias'))->toBe('123 `alias`');

test::case('测试带别名的布尔值', (string)(new value(true))->as('alias'))->toBe('TRUE `alias`');

test::case('测试带别名的 null 值', (string)(new value(null))->as('alias'))->toBe('NULL `alias`');

test::case('测试带别名的数组值', (string)(new value([1, 2, 3]))->as('alias'))->toBe('1,2,3 `alias`');
