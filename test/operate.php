<?php
include_once '../vendor/autoload.php';
error_reporting(E_ALL);

use nx\helpers\db\sql;
use nx\helpers\db\sql\operate;
use nx\helpers\db\sql\value;
use nx\test\test;

// 测试 operate 类的功能
test::case('operate 构造函数', new operate('eq', [1, 2]))
	->toBeInstanceOf(operate::class);

// 测试基本操作符
test::case('基本操作符: eq', (string)(new operate('eq', ['a', 'b'])))
	->toBe('"a" = "b"');

test::case('基本操作符: ne', (string)(new operate('ne', ['a', 'b'])))
	->toBe('"a" != "b"');

test::case('基本操作符: lt', (string)(new operate('lt', ['a', 'b'])))
	->toBe('"a" < "b"');

test::case('基本操作符: le', (string)(new operate('le', ['a', 'b'])))
	->toBe('"a" <= "b"');

test::case('基本操作符: gt', (string)(new operate('gt', ['a', 'b'])))
	->toBe('"a" > "b"');

test::case('基本操作符: ge', (string)(new operate('ge', ['a', 'b'])))
	->toBe('"a" >= "b"');

test::case('基本操作符: add', (string)(new operate('add', ['a', 'b'])))
	->toBe('"a" + "b"');

test::case('基本操作符: sub', (string)(new operate('sub', ['a', 'b'])))
	->toBe('"a" - "b"');

test::case('基本操作符: mul', (string)(new operate('mul', ['a', 'b'])))
	->toBe('"a" * "b"');

test::case('基本操作符: div', (string)(new operate('div', ['a', 'b'])))
	->toBe('"a" / "b"');

test::case('基本操作符: mod', (string)(new operate('mod', ['a', 'b'])))
	->toBe('"a" % "b"');

// 测试 not_ 前缀
test::case('not_ 前缀处理: not_eq', (string)(new operate('not_eq', ['a', 'b'])))
	->toBe('"a" != "b"');

test::case('not_ 前缀处理: not_like', (string)(new operate('not_like', ['a', 'b'])))
	->toBe('NOT "a" LIKE "b"');

// 测试 like 和 regexp
test::case('操作符: like', (string)(new operate('like', ['a', 'b'])))
	->toBe('"a" LIKE "b"');

test::case('操作符: rlike', (string)(new operate('rlike', ['a', 'b'])))
	->toBe('"a" REGEXP "b"');

test::case('操作符: regexp', (string)(new operate('regexp', ['a', 'b'])))
	->toBe('"a" REGEXP "b"');

// 测试 between
test::case('操作符: between', (string)(new operate('between', ['a', 'b', 'c'])))
	->toBe('"a" BETWEEN "b" AND "c"');

test::case('操作符: not_between', (string)(new operate('not_between', ['a', 'b', 'c'])))
	->toBe('"a" NOT BETWEEN "b" AND "c"');

// 测试逻辑操作符
test::case('逻辑操作符: and', (string)(new operate('and', ['a', 'b'])))
	->toBe('("a" AND "b")');

test::case('逻辑操作符: or', (string)(new operate('or', ['a', 'b'])))
	->toBe('("a" OR "b")');

test::case('逻辑操作符: xor', (string)(new operate('xor', ['a', 'b'])))
	->toBe('("a" XOR "b")');

// 测试自定义函数
test::case('自定义函数: in', (string)(new operate('in', ['a', 'b', 'c'])))
	->toBe('"a" IN ("b", "c")');

test::case('自定义函数: not_in', (string)(new operate('not_in', ['a', 'b', 'c'])))
	->toBe('"a" NOT IN ("b", "c")');

// 测试函数调用
test::case('函数调用: avg', (string)(new operate('avg', ['a'])))
	->toBe('AVG("a")');

test::case('函数调用: count', (string)(new operate('count', ['a'])))
	->toBe('COUNT("a")');

test::case('函数调用: min', (string)(new operate('min', ['a'])))
	->toBe('MIN("a")');

test::case('函数调用: max', (string)(new operate('max', ['a'])))
	->toBe('MAX("a")');

test::case('函数调用: sum', (string)(new operate('sum', ['a'])))
	->toBe('SUM("a")');

// 测试别名
test::case('MySQL内置函数调用: trim', (string)(sql::TRIM('a')))
	->toBe('TRIM(FROM "a")');

// 继续通过 sql::SUM() 方式(替换SUM为MySQL内置函数名)测试mysql内置函数调用
test::case('MySQL内置函数调用: weight_string with type and length', (string)(sql::WEIGHT_STRING('a', 10)))
	->toBe('WEIGHT_STRING("a" AS CHAR(10))');

test::case('MySQL内置函数调用: avg with distinct', (string)(sql::AVG(true, true)))
	->toBe('AVG(DISTINCT TRUE)');

test::case('MySQL内置函数调用: pi', (string)(sql::PI()))
	->toBe('PI()');
