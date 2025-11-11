<?php

include_once '../vendor/autoload.php';
error_reporting(E_ALL);

use nx\helpers\db\sql;
use nx\helpers\db\sql\part;
use nx\helpers\db\sql\part\type;
use nx\test\test;

// ====================
// 基本值类型测试（不启用参数收集）
// ====================
$sql = new sql('users');
$sql->collectParams = false;

test::case("基本整型值", (string)new part(1, type::VALUE, $sql))->toBe('1');
test::case("基本字符串值", (string)new part('abc', type::VALUE, $sql))->toBe('"abc"');
test::case("带别名的字符串值", (string)new part('abc', type::VALUE, $sql)->as('name'))->toBe('"abc" `name`');

// ====================
// 字段类型测试
// ====================
test::case("字段名字符串", (string)new part('id', type::FIELD, $sql))->toBe('`id`');
test::case("字段名为数字", (string)new part('123', type::FIELD, $sql))->toBe('`123`');
test::case("使用 offsetGet 获取字段", (string)$sql['id'])->toBe('`id`');

// ====================
// 函数类型测试
// ====================
test::case("函数无参数", (string)new part('sum', type::FUNCTION, $sql))->toBe('SUM()');
test::case("函数带参数", (string)(new part('sum', type::FUNCTION, $sql)->arguments('*')))->toBe('SUM(*)');

test::case("CONCAT 函数多个参数", (string)sql::CONCAT($sql['name'], 'hello'))->toBe('CONCAT(`name`, "hello")')->and($sql->params)->toBe([]);

test::case("静态函数调用 YEAR", (string)sql::YEAR('2019-04-19'))->toBe('YEAR("2019-04-19")');
test::case("YEAR 使用占位符", (string)sql::YEAR($sql('2019-04-19')))->toBe('YEAR("2019-04-19")')->and($sql->params)->toBe([]);

test::case("SUM * 通配符", (string)sql::SUM($sql['*']))->toBe('SUM(*)');

// ====================
// 自定义函数操作测试
// ====================
test::case("运算符 = ", (string)sql::operate(1, 2))->toBe('1 = 2');
test::case("运算符 >", (string)sql::operate(1, 2, '>'))->toBe('1 > 2');
test::case("运算符 >= ", (string)sql::operate(1, 2, '>='))->toBe('1 >= 2');
test::case("NOT LIKE", (string)sql::operate(1, 2, 'NOT LIKE'))->toBe('1 NOT LIKE 2');

test::case("AND 运算符", (string)sql::and("1", 2))->toBe('("1" AND 2)');
test::case("BETWEEN", (string)sql::between(2, 1, 3))->toBe('2 BETWEEN 1 AND 3');
test::case("NOT BETWEEN", (string)sql::between(1, 2, 3)->negate())->toBe('1 NOT BETWEEN 2 AND 3');

test::case("IN 操作", (string)sql::in(1, 1, 2, 3))->toBe('1 IN (1,2,3)');
test::case("NOT IN 操作", (string)sql::not_in(1, 1, 2, 3))->toBe('1 NOT IN (1,2,3)');

test::case("IF 函数", (string)sql::IF(true, 'true', 'false'))->toBe('IF(TRUE, "true", "false")');

// ====================
// 字符串处理函数
// ====================
test::case("TRIM 函数", (string)sql::TRIM(' 123 '))->toBe('TRIM(BOTH FROM " 123 ")');
test::case("TRIM 带方向", (string)sql::TRIM(' 123 ', 'x', 'TRAILING'))->toBe('TRIM(TRAILING "x" FROM " 123 ")');

test::case("WEIGHT_STRING 函数", (string)sql::WEIGHT_STRING('abc', null, 'char'))->toBe('WEIGHT_STRING("abc" AS CHAR())');
test::case("WEIGHT_STRING 带长度", (string)sql::WEIGHT_STRING('abc', 4, 'byte'))->toBe('WEIGHT_STRING("abc" AS BYTE(4))');

// ====================
// 算术运算符测试
// ====================
test::case("加法运算", (string)sql::add(1, 2))->toBe('1 + 2');
test::case("大于比较", (string)sql::gt(1, 2))->toBe('1 > 2');
test::case("取反大于", (string)sql::gt(1, 2)->negate())->toBe('NOT (1 > 2)');
test::case("not_gt 别名", (string)sql::not_gt(1, 2))->toBe('NOT (1 > 2)');
test::case("等于比较", (string)sql::operate('a', 'b', '='))->toBe('"a" = "b"');
test::case("大于比较", (string)sql::operate('a', 'b', '>'))->toBe('"a" > "b"');
// ====================
// 链式调用测试
// ====================
test::case("函数链式调用 arguments + as", (string)(new part('sum', type::FUNCTION, $sql)->arguments('*')->as('total')))->toBe('SUM(*) `total`');

// ====================
// 特殊情况 & 边界测试
// ====================
test::case("空字符串字段", (string)new part('', type::FIELD, $sql))->toBe('``');
test::case("通配符字段", (string)new part('*', type::FIELD, $sql))->toBe('*');

// ====================
// 参数收集与占位符测试（重新设置）
// ====================
$sql = new sql('users');
$sql->collectParams = true;

test::case("参数收集 - 字符串值", (string)$sql('hello'))->toBe('?')->and($sql->params)->toBe(['hello']);

$sql = new sql('users');
$sql->collectParams = true;
test::case("函数参数自动转换为占位符", (string)sql::CONCAT($sql['name'], $sql(type::VALUE)))->toBe('CONCAT(`name`, ?)')->and($sql->params)->toBe([type::VALUE]);

// ====================
// 调用__call和__callStatic时的行为测试（重新设置）
// ====================
$sql = new sql('users');
test::case("调用函数方法 sum", (string)$sql['score']->sum())->toBe('SUM(`score`)');
test::case("调用函数方法 concat", (string)$sql['name']->concat($sql['email']))->toBe('CONCAT(`name`, `email`)');

// 注意：字符串不会自动识别为字段！
test::case("函数调用中传入字符串不转字段", (string)$sql['score']->sum('field_name'))->toBe('SUM(`score`, ?)')->and($sql->params)->toBe(['field_name']);

// ====================
// negate 测试
// ====================
test::case("NOT LIKE 调用", (string)sql::like(1, 2)->negate())->toBe('1 NOT LIKE 2');
test::case("NOT BETWEEN 调用", (string)sql::between(1, 2, 3)->negate())->toBe('1 NOT BETWEEN 2 AND 3');

// ====================
// 静态方法调用函数名转义处理
// ====================
test::case("函数名转大写", (string)sql::avg($sql['score']))->toBe('AVG(`score`)');
test::case("函数名包含特殊字符", (string)sql::count($sql['*']))->toBe('COUNT(*)');



