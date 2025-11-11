<?php
include_once '../vendor/autoload.php';
error_reporting(E_ALL);

// 使用测试框架
use nx\test\test;

// 为了简化测试，这里定义一个简化版本的 result 类，只包含 map 方法
class testResult extends \nx\helpers\db\pdo\result{
	private array $data;
	public function __construct(bool $result, array $data){
		$this->result = $result;
		$this->data = $data;
	}
	/**
	 * 模拟 PDO 的 fetchAll 返回结果
	 */
	public function fetchAll(...$args): ?array{
		$this->result = true;
		return $this->data;
	}
}
// 构建测试数据
$data = [
	['id' => 1, 'name' => 'Alice', 'age' => 25],
	['id' => 2, 'name' => 'Bob', 'age' => 30],
	['id' => 3, 'name' => 'Charlie', 'age' => 35],
];

$result = new testResult(true, $data);

// === 测试 map 方法的各种场景 ===

// 测试1: 按键获取值
test::case("测试 map 方法 - 按键获取值", $result->map('id', 'name'))->toEqual([
	1 => 'Alice',
	2 => 'Bob',
	3 => 'Charlie',
]);

// 测试2: 使用闭包处理数据
test::case("测试 map 方法 - 使用闭包处理", $result->map('id', fn($item) => $item['name'] . '(' . $item['age'] . ')'))->toEqual([
	1 => 'Alice(25)',
	2 => 'Bob(30)',
	3 => 'Charlie(35)',
]);

// 测试3: 返回完整数组项
test::case("测试 map 方法 - 不使用闭包，仅返回完整数组", $result->map('id', null))->toEqual([
	1 => ['id' => 1, 'name' => 'Alice', 'age' => 25],
	2 => ['id' => 2, 'name' => 'Bob', 'age' => 30],
	3 => ['id' => 3, 'name' => 'Charlie', 'age' => 35],
]);

// 测试4: 不指定 key，按默认索引返回值
test::case("测试 map 方法 - 不指定 key 使用默认索引", $result->map(null, 'name'))->toEqual([
	'Alice',
	'Bob',
	'Charlie',
]);

// 测试5: 不指定 key，使用闭包处理
test::case("测试 map 方法 - 不指定 key 使用闭包处理", $result->map(null, fn($item) => $item['name'] . '(' . $item['age'] . ')'))->toEqual([
	'Alice(25)',
	'Bob(30)',
	'Charlie(35)',
]);

// 测试6: 不指定 key 和 value，返回全部数据
test::case("测试 map 方法 - 不指定 key 和 value 返回全部数据", $result->map(null, null))->toEqual($data);