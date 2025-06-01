<?php

use nx\helpers\db\sql;
use nx\helpers\db\sql\part;
use nx\test\test;

include_once '../vendor/autoload.php';
error_reporting(E_ALL);

$sql = new sql('test');
$sql->collectParams = false;

//基本类型
test::case('int', (string)new part(1, 'value', $sql))->toBe('1');
test::case('string', (string)new part('1', 'value', $sql))->toBe('"1"');
test::case('string', (string)new part('abc', 'value', $sql))->toBe('"abc"');
test::case('string as', (string)new part('abc', 'value', $sql)->as('name'))->toBe('"abc" `name`');

$sql->collectParams = true;
//sql中的数值
$sql = new sql('test');
test::case('value from sql', (string)$sql('123'))->toBe('?')
	->and($sql->params)->toBe(["123"]);

//field
test::case('field string', (string)new part('id', 'field', $sql))->toBe('`id`');
test::case('field int', (string)new part('123', 'field', $sql))->toBe('`123`');
test::case('field from', (string)$sql['id'])->toBe('`id`');

//function
$part = new part('sum', 'function', $sql);
test::case('function', (string)$part)->toBe('SUM()');
$part = new part('sum', 'function', $sql);
$part->arguments('*');
test::case('function with arg', (string)$part)->toBe('SUM(*)');

$sql = new sql('test');
$part = new part('CONCAT', 'function', $sql);
$part->arguments('*', 'abc', 123);
test::case('function with args', (string)$part)->toBe('CONCAT(*, ?, ?)')
	->and($sql->params)->toBe(["abc", 123]);

$part = new part('CONCAT', 'function', $sql);
$part->arguments($sql['id'], $sql['score']);
test::case('function with args', (string)$part)->toBe('CONCAT(`id`, `score`)');
//sql function
test::case('year', (string)sql::YEAR('2019-04-19'))->toBe('YEAR("2019-04-19")');
$sql = new sql('test');
test::case('year', (string)sql::YEAR($sql('2019-04-19')))->toBe('YEAR(?)')
	->and($sql->params)->toBe(["2019-04-19"]);
test::case('year field', (string)sql::YEAR($sql['createdAt']))->toBe('YEAR(`createdAt`)');
test::case('sum *', (string)sql::SUM($sql['*']))->toBe('SUM(*)');
//sql operate
test::case('=', (string)sql::operate(1,2))->toBe('1 = 2');
test::case('>', (string)sql::operate(1,2, '>'))->toBe('1 > 2');
test::case('>=', (string)sql::operate(1,2, '>='))->toBe('1 >= 2');
test::case('not like', (string)sql::operate(1,2, 'NOT LIKE'))->toBe('1 NOT LIKE 2');
test::case('and', (string)sql::operate("1",2, 'and'))->toBe('"1" AND 2');
test::case('and', (string)sql::and("1",2))->toBe('("1" AND 2)');
test::case('between', (string)sql::between(2,1,3))->toBe('2 BETWEEN 1 AND 3');
test::case('between not', (string)sql::between(1,2,3, true))->toBe('1 NOT BETWEEN 2 AND 3');
test::case('in', (string)sql::in(1,1,2,3))->toBe('1 IN (1,2,3)');
test::case('not in', (string)sql::notIn(1,1,2,3))->toBe('1 NOT IN (1,2,3)');
test::case('if', (string)sql::IF(true, 'true', 'false'))->toBe('IF(TRUE, "true", "false")');
test::case('if', (string)sql::IFIF(true, 'true', 'false'))->toBe('IF(TRUE, "true", "false")');
test::case('if', (string)sql::IFIF(null, 'true', 'false'))->toBe('IF(NULL, "true", "false")');
test::case('if', (string)sql::IFIF('*', '\*', 'no'))->toBe('IF(*, "*", "no")');
test::case('trim', (string)sql::TRIM(' 123 '))->toBe('TRIM(BOTH FROM " 123 ")');
test::case('trim', (string)sql::TRIM(' 123 ', 'x', 'TRAILING'))->toBe('TRIM(TRAILING "x" FROM " 123 ")');
test::case('weight', (string)sql::WEIGHT_STRING('abc', null, 'char'))->toBe('WEIGHT_STRING("abc" AS CHAR())');
test::case('weight', (string)sql::WEIGHT_STRING('abc', 4, 'byte'))->toBe('WEIGHT_STRING("abc" AS BYTE(4))');

