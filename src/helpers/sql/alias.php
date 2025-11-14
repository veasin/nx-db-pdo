<?php
declare(strict_types=1);
namespace nx\helpers\db\sql;

trait alias{
	protected(set) ?string $alias = null;
	public function as(string $alias): static{
		$clone = clone $this;
		$clone->alias = $alias;
		return $clone;
	}
}
