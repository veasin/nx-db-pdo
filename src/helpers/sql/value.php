<?php
declare(strict_types=1);
namespace nx\helpers\db\sql;

use nx\helpers\db\sql;

class value extends expr{
	public function __construct(protected(set) mixed $value){}
	public function __toString(): string{
		if($this->value === '*') return '*';
		if($this->value === '\*') $this->value = '*';
		if(sql::$current){
			if(is_array($this->value)){
				return join(',', array_map(sql::$current->collectParam(...), $this->value));
			}
			return sql::$current->collectParam($this->value);
		}
		$v = match (true) {
			is_string($this->value) => "\"$this->value\"",
			is_bool($this->value) => $this->value ? 'TRUE' : 'FALSE',
			is_null($this->value) => 'NULL',
			is_array($this->value) => join(',', $this->value),
			default => (string)$this->value,
		};
		return $this->alias ? "$v `$this->alias`" : $v;
	}
}