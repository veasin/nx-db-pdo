<?php
declare(strict_types=1);
namespace nx\helpers\db\pdo;

class result{
	public function __construct(
		protected bool $result,
		protected ?\PDOStatement $sth = null,
		protected ?\PDO $pdo = null
	){}
	public function ok():bool{
		return $this->result;
	}
	/**
	 * $db->update() <=false
	 *      ->affectedRows();
	 *
	 */
	public function rowCount():?int{
		return $this->result ? $this->sth?->rowCount() : null;
	}
	public function lastInsertId():?int{
		return $this->result ? (int)$this->pdo?->lastInsertId() : null;
	}
	public function first($className=null, ...$args):mixed{
		if(!$this->result) return null;
		$r =$className
			? $this->sth->fetchObject($className, $args)
			: $this->sth->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_FIRST);
		return $r ?: null;
	}
	public function all($className=null, ...$args):?array{
		if(!$this->result) return null;
		return $className
			? $this->sth->fetchAll(\PDO::FETCH_CLASS, $className, $args)
			: $this->sth->fetchAll(\PDO::FETCH_ASSOC);
	}
	/**
	 * 获取全部查询结果 后，再对全部数据进行一次回调，根据参数不同进行不同返回
	 * DATA:[[key, val, oth],[key, val, oth]...]
	 * ARGS:(key, val)=>[key=>val],
	 *      (key, fun)=>[key=>fun(val)]
	 *      (key, null)=>[key=>[key, val, oth]],
	 *      (null, val)=>[val, val],
	 *      (null, fun)=>[fun(val)]
	 *      (null, null) =>$array
	 *
	 * @param int|string|null          $key
	 * @param int|string|null|callable $value
	 * @return array
	 */
	public function map(int|string|null $key = null, int|string|null|callable $value = null): array{
		if(!$this->result) return [];
		$data = $this->fetchAll(\PDO::FETCH_ASSOC);
		if(empty($data)) return [];
		if(null === $key && null === $value) return $data;
		$result = [];
		foreach($data as $item){
			$k = $key !== null ? $item[$key] ?? null : null;
			$v = match (true) {
				null === $value => $item,
				is_callable($value) => $value($item),
				default => $item[$value] ?? null,
			};
			$key === null ? $result[] = $v : $result[$k] = $v;
		}
		return $result;
	}
	/**
	 * 获取全部查询结果 后，再对全部数据进行一次回调
	 * @param $callback
	 * @param ...$fetch_styles
	 * @return mixed
	 */
	public function fetchAllMap($callback, ...$fetch_styles): mixed{
		$fetch_styles = $fetch_styles ?: [\PDO::FETCH_ASSOC];
		$r=$this->fetchAll(...$fetch_styles);
		return call_user_func($callback, $r);
	}
	/**
	 * 获取全部查询结果 后，再针对每条数据进行回调处理
	 * @param $callback
	 * @param ...$fetch_styles
	 * @return array|null
	 */
	public function fetchMap($callback, ...$fetch_styles):?array{
		$fetch_styles = $fetch_styles ?: [\PDO::FETCH_ASSOC];
		$r=$this->fetchAll(...$fetch_styles);
		if(null===$r) return null;
		$rr=[];
		foreach($r as $index=>$array){
			$rr[]=call_user_func($callback, $array, $index);
		}
		return $rr;
	}
	/**
	 * 获取一条查询结果
	 * @param ...$args
	 * @return array|null
	 */
	public function fetch(...$args):?array{
		return $this->result ? ($this->sth->fetch(...$args) ?: null) : null;
	}
	/**
	 * 获取全部查询结果
	 * @param ...$args
	 * @return array|null
	 */
	public function fetchAll(...$args):?array{
		return $this->result ? ($this->sth->fetchAll(...$args) ?: null) : null;
	}
}