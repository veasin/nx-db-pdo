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
		return builder::value($this->value, $this->alias, sql::$current?->dialect);
	}
	public function __debugInfo(): ?array{
		$i =['value'=>$this->value];
		$this->alias && $i['alias'] = $this->alias;
		return $i;
	}
}