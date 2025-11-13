<?php
include_once '../vendor/autoload.php';
error_reporting(E_ALL);
use nx\test\test;
use nx\helpers\db\sql\value;
use nx\helpers\db\sql\field;
use nx\helpers\db\sql\operate;
use nx\helpers\db\sql\table;
use nx\helpers\db\sql\expr;
use nx\helpers\db\sql;

// 复复杂 SQL 测试用例
test::case("测试 complex select with join", (string)(new sql(new table("users"))->select(['id', 'name'])->join(new table("posts"), ['user_id' => 'id'])))->toMatch("/^SELECT.*FROM.*`users` JOIN.*`posts`/");

test::case("测试 complex select with where and order", (string)(new sql(new table("users"))->select('*')->where(['status' => 'active'])->sort('created_at', 'DESC')))
	->toMatch("/^SELECT.*WHERE.*status.*=.*active.*ORDER BY.*created_at DESC/");

test::case("测试 complex select with group and having", (string)(new sql(new table("orders"))->select(['user_id', 'COUNT(*) as count'])->group('user_id')->having(['count' => ['fn' => 'gt', 1]])))
	->toMatch("/^SELECT.*GROUP BY.*HAVING.*>.*1/");

test::case("测试 complex select with distinct and limit", (string)(new sql(new table("products"))->select(['DISTINCT category'])->limit(10, 5)->sort('name')))->toMatch("/^SELECT DISTINCT.*LIMIT 5, 10/");

test::case("测试 insert with options", (string)(new sql(new table("users"))->insert(['name' => 'John'], ['ignore' => true])))->toMatch("/^INSERT IGNORE.*INTO.*`users`/");

test::case("测试 update with where and limit",
	(string)(new sql(new table("users"))->update(['status' => 'inactive'])->where(['last_login' => ['fn' => 'lt', date('Y-m-d H:i:s', strtotime('-1 year'))]])->limit(100))
)->toMatch("/^UPDATE.*SET.*status.*=.*inactive.*WHERE.*<.*1 year.*LIMIT 100/");

test::case("测试 delete with where", (string)(new sql(new table("logs"))->delete()->where(['created_at' => ['fn' => 'lt', date('Y-m-d H:i:s', strtotime('-30 days'))]])))
	->toMatch("/^DELETE.*FROM.*`logs`.*WHERE.*<.*30 days/");

test::case("测试 complex operation with nested calls", (string)(new operate("add", [new operate("mul", [2, 3]), new operate("sub", [5, 1])])))->toBe("(2 * 3 + 5 - 1)");

test::case("测试 field with method call", (string)(new field("price")->gt(100)))->toBe("`price` > 100");

test::case("测试 value with method call", (string)(new value("hello")->like("%world%")))->toBe("'hello' LIKE '%world%'");

test::case("测试 operate with not_", (string)(new operate("not_eq", [1, 2])))->toBe("(1 != 2)");

test::case("测试 operate with between", (string)(new operate("between", [5, 1, 10])))->toBe("5 BETWEEN 1 AND 10");

test::case("测试 operate with in", (string)(new operate("in", ["id", 1, 2, 3])))->toBe("`id` IN (1, 2, 3)");

test::case("测试 complex select with multiple joins and conditions",
	(string)((new sql(new table("users")))->select(['u.name', 'p.title'])->join(new table("posts")->as("p"), ['user_id' => 'id'], ['LEFT'])->join(new table("categories")->as("c"),
			['p.category_id' => 'id'],
			['INNER']
		)->where([
			'u.status' => 'active',
			'p.published' => true,
			'c.name' => ['fn' => 'in', ['tech', 'science']]
		])->sort(['u.created_at', 'p.updated_at'], 'DESC'))
)->toMatch("/^SELECT.*FROM.*`users` LEFT JOIN.*`posts` INNER JOIN.*`categories`/");

test::case("测试 complex select with aggregate functions",
	(string)((new sql(new table("sales")))->select([
		'COUNT(*) as total',
		'SUM(amount) as total_amount',
		'AVG(amount) as avg_amount'
	])->group('user_id')->having(['total_amount' => ['fn' => 'gt', 1000]]))
)->toMatch("/^SELECT.*COUNT$\*$.*SUM.*AVG.*GROUP BY.*HAVING/");

test::case("测试 select with complex field expressions",
	(string)((new sql(new table("products")))->select([
		new operate('add', [new operate('mul', ['price', 1.2]), 5]),
		'name'
	]))
)->toMatch("/^SELECT.*$\`price\` \* 1\.2 \+ 5$.*FROM.*`products`/");
