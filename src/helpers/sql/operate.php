<?php
declare(strict_types=1);
namespace nx\helpers\db\sql;

class operate extends expr{
	protected bool $negate = false;
	public function __construct(protected(set) string $name, protected(set) array $arguments){
		foreach($this->arguments as &$arg){
			if(!$arg instanceof expr) $arg = new value($arg);
		}
	}
	public function __toString(): string{
		$func = $this->name;
		if(str_starts_with($func, 'not_')){
			$func = substr($func, 4); // 移除 'not_' 前缀
			$this->negate = !$this->negate;
		}
		//$args = array_map(fn($arg) => (string)$arg, $this->arguments);
		$args = $this->arguments;
		// 操作符处理
		$map = [
			'add' => '+', 'sub' => '-', 'mul' => '*', 'div' => '/', 'mod' => '%',
			'eq' => '=', 'ne' => '!=', 'lt' => '<', 'le' => '<=', 'gt' => '>', 'ge' => '>=', 'nullsafe_eq' => '<=>',
			'equal'=>'=',//兼容
			'and' => 'AND', 'or' => 'OR', 'xor' => 'XOR',
			'like' => 'LIKE', 'rlike' => 'REGEXP', 'regexp' => 'REGEXP',
			'between' => 'BETWEEN',
		];
		$result = match($func) {
			'equal',
			'add', 'sub', 'mul', 'div', 'mod',
			'eq', 'ne', 'lt', 'gt', 'le', 'ge', 'nullsafe_eq' => "$args[0] $map[$func] $args[1]",
			'and', 'or', 'xor' => "($args[0] $map[$func] $args[1])",
			'like', 'rlike', 'regexp' => "$args[0] ".($this->negate ?"NOT ":"").$map[$func]." $args[1]",
			'between' => "$args[0] ".($this->negate ?"NOT ":"").$map[$func]." $args[1] AND $args[2]",
			default => null,
		};
		if(null !== $result) return $this->alias ? "$result `$this->alias`" : $result;
		// 特殊函数处理（有额外参数）
		switch($func){
			case 'operate':
				$opt = $args[2] ?? '=';
				if($opt instanceof expr) $opt = $opt->value ?? $opt;
				$opt = strtoupper($opt);
				$result = "$args[0] $opt $args[1]";
				break;
			case 'in':
				$in = $args;
				array_shift($in);
				$in = implode(',', $in);
				$not = $this->negate ? "NOT " : "";
				$result = "{$args[0]} {$not}IN ($in)";
				break;
			//case 'notIn':
			//	$in = $args;
			//	array_shift($in);
			//	$in = implode(',', $in);
			//	$not =$this->negate ?"":"NOT ";
			//	$r = "{$args[0]} {$not}IN ($in)";
			//	break;
			case 'TRIM':
				$side = strtoupper(($args[2] ?? null) ? $args[2]->value : 'both');
				$rem = ($args[1] ?? null) ? "{$args[1]} " : '';
				$result = "TRIM($side {$rem}FROM {$args[0]})";
				break;
			case 'WEIGHT_STRING':
				$type = strtoupper(($args[2] ?? null) ? $args[2]->value : 'char');
				$n = ($args[1] ?? null) ? ($args[1]->value ?? null) ? $args[1] : '' : '';
				$result = "WEIGHT_STRING({$args[0]} AS $type($n))";
				break;
			case "AVG":
			case "COUNT":
			case "MIN":
			case "MAX":
			case "SUM":
				$fun = strtoupper($func);
				$distinct = (!empty($args[1]) && $args[1]->value) ? 'DISTINCT ' : '';
				$result = "$fun($distinct{$args[0]})";
				break;
			//case "IFIF":
			//	$func = "IF";
			default:
				$result = strtoupper($func)."(" . implode(', ', $args) . ")";
				break;
		}
		if($this->negate && !in_array($func, ['in','notIn', 'between', 'like', 'rlike','regexp'])){
			return "NOT ($result)".($this->alias ?" `$this->alias`":'');
		}
		return $this->alias ? "$result `$this->alias`" : $result;
	}
}