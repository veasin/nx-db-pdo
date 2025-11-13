<?php
declare(strict_types=1);
namespace nx\helpers\db\sql;

use nx\helpers\db\sql;

class field extends expr{
	public function __construct(readonly protected string $name, readonly protected ?table $table = null){}
	public function __toString(): string{
		$field = "*" === $this->name ? "*" : "`$this->name`";
		if($this->table && sql::$current?->hasJoin()){
			$table = $this->table->alias ?? $this->table->name;
			$field = "`$table`.$field";
		}
		return $this->alias ? "$field `$this->alias`" : $field;
	}
}