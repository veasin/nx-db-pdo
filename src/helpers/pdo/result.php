<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/05/05 005
 * Time: 17:30
 */
declare(strict_types=1);
namespace nx\helpers\db\pdo;

class result{
	/**
	 * @var \PDOStatement|null
	 */
	protected ?\PDOStatement $sth=null;
	/**
	 * @var \PDO|null
	 */
	protected ?\PDO $pdo;
	/**
	 * @var bool
	 */
	protected bool $result=false;
	public function __construct(bool $result, \PDOStatement $sth=null, \PDO $pdo=null){
		$this->sth=$sth;
		$this->pdo=$pdo;
		$this->result=$result;
	}
	public function ok():bool{
		return $this->result;
	}
	/**
	 * $db->update() <=false
	 *      ->affectedRows();
	 *
	 */
	public function rowCount():?int{
		if(false === $this->result) return null;else return $this->sth->rowCount();
	}
	public function lastInsertId():?int{
		if(false === $this->result) return null;else return (int)$this->pdo->lastInsertId();
	}
	public function first($className=null, ...$args){
		if(false === $this->result) return null;elseif(null === $className) return $this->fetch($this->pdo::FETCH_ASSOC, $this->pdo::FETCH_ORI_FIRST);
		else{
			$o=$this->sth->fetchObject($className, $args);
			return false !== $o ?$o :null;
		}
	}
	public function all($className=null, ...$args):?array{
		if(false === $this->result) return null;elseif(null === $className) return $this->fetchAll($this->pdo::FETCH_ASSOC);
		else return $this->fetchAll($this->pdo::FETCH_CLASS, $className, $args);
	}
	/**
	 * DATA:[[key, val, oth],[key, val, oth]...]
	 * ARGS:(key, val)=>[key=>val],
	 *      (key, fun)=>[key=>fun(val)]
	 *      (key,false)=>[key=>[key, val, oth]],
	 *      (null, val)=>[val, val],
	 *      (null, fun)=>[fun(val)]
	 *      (null, false) =>$array
	 * @param int|string|null $key
	 * @param mixed             $value
	 * @return array
	 */
	public function map(int|string|null $key=0, mixed $value=1):array{
		$callback=function($array) use ($key, $value){
			if(!is_array($array)) return $array;
			$r=[];
			if(is_null($key)){
				if($value === false) return $array;
				foreach($array as $_key=>$_value){
					$r[]=is_callable($value) ?$value($_value, $_key) :$_value[$value];
				}
			}else{
				foreach($array as $_key=>$_value){
					$r[$_value[$key]]=($value === false) ?$_value :(is_callable($value) ?$value($_value, $_key) :$_value[$value]);
				}
			}
			return $r;
		};
		return $this->fetchAllMap($callback, $this->pdo::FETCH_ASSOC);
	}
	public function fetchAllMap($callback, ...$fetch_styles){
		if(0 === count($fetch_styles)) $fetch_styles[]=$this->pdo::FETCH_ASSOC;
		$r=$this->fetchAll(...$fetch_styles);
		return call_user_func($callback, $r);
	}
	public function fetchMap($callback, ...$fetch_styles):?array{
		if(0 === count($fetch_styles)) $fetch_styles[]=$this->pdo::FETCH_ASSOC;
		$r=$this->fetchAll(...$fetch_styles);
		if(is_array($r)){
			$rr=[];
			foreach($r as $index=>$array){
				$rr[]=call_user_func($callback, $array, $index);
			}
			return $rr;
		}else return $r;
	}
	public function fetch(...$args):?array{
		if(false === $this->result) return null;else{
			$r=$this->sth->fetch(...$args);
			return false !== $r ?$r :null;
		}
	}
	public function fetchAll(...$args):?array{
		if(false === $this->result) return null;else{
			$r=$this->sth->fetchAll(...$args);
			return false !== $r ?$r :null;
		}
	}
}