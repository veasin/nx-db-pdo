<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/17 017
 * Time: 11:37
 */
declare(strict_types=1);
namespace nx\helpers\db;

use nx\helpers\db\pdo\result;

class pdo{
	private array $_nx_db_pdo_options=[
		\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC,
		\PDO::ATTR_STRINGIFY_FETCHES=>false,
		\PDO::ATTR_EMULATE_PREPARES=>false,
	];
	private int $timeout;//超时
	/**
	 * @var \PDO|null
	 */
	public ?\PDO $link=null;
	private array $setup;
	/**
	 * @var callable
	 */
	private $_log=null;
	public function __construct($setup=[]){
		$this->setup=$setup ?? [];
		$this->timeout=$this->setup['timeout'] ?? 0;
		$this->setup['options']=($this->setup['options'] ?? []) + $this->_nx_db_pdo_options;
	}
	/**
	 * @return \PDO
	 */
	private function db():\PDO{
		$now=time();
		if(null === $this->link || ($this->timeout > 0 && $this->timeout < $now)){
			$this->link=new \PDO($this->setup['dsn'], $this->setup['username'], $this->setup['password'], $this->setup['options']);
			$this->timeout=(($this->setup['timeout'] ?? 0) > 0) ?$now + $this->setup['timeout'] :0;
		}
		return $this->link;
	}
	public function setLog(callable $logger): void{
		$this->_log=$logger;
	}
	/**
	 * @param string $template
	 * @param array  $data
	 */
	private function log(string $template, array $data=[]): void{
		if(null !== $this->_log){
			call_user_func($this->_log, $template , 'sql');
			count($data) && call_user_func($this->_log, $data);
		}
	}
	public function logFormatSQL(string $prepare, array $params=null, string $action=''): void{
		$params=$params ?? [];
		$sql=str_replace('?', '%s', $prepare);
		$prefix='  ';
		$map=function($value){
			return gettype($value) === 'integer' ?$value :"\"$value\"";
		};
		if('insert' === $action){
			$_first=current($params);
			if(is_array($_first)){
				foreach($params as $param){
					$this->log($prefix.sprintf($sql, ...array_map($map, $param)));
				}
				return;
			}
		}
		$this->log($prefix.sprintf($sql, ...array_map($map, $params)));
	}
	/**
	 * @return result|null
	 */
	private function failed():?result{
		$this->log('sql error: %s %s %s', $this->link->errorInfo());
		return new result(false, null, $this->db());
	}
	/**
	 * 直接插入方法
	 * ->insert('INSERT INTO cds (`interpret`, `title`) VALUES (?, ?)', ['vea', 'new cd']);
	 * @param string     $sql
	 * @param array|null $params
	 * @return result
	 */
	public function insert(string $sql, array $params=null):pdo\result{
		$this->logFormatSQL($sql, $params, 'insert');
		$db=$this->db();
		$ok=false;
        $sth = null;
		if(0 === count($params)){
			$ok=$db->exec($sql);
            if(false !==$ok) $ok =true;
		}else{
			$sth=$db->prepare($sql);
			if(false === $sth) return $this->failed();
			$_first=current($params);
			if(!is_array($_first)){
				$ok=$sth->execute($params);
			}else{
				foreach($params as $_fields){
					$ok=$sth->execute($_fields);
				}
			}
		}
		return new result($ok, $sth, $db);
	}
	/**
	 * 选择记录
	 * ->select('SELECT `cds`.* FROM `cds` WHERE `cds`.`id` = ?', [13])
	 * @param string     $sql
	 * @param array|null $params
	 * @return result
	 */
	public function select(string $sql, array $params=null):pdo\result{
		$this->logFormatSQL($sql, $params);
		$db=$this->db();
		$sth=$db->prepare($sql);
		if(false === $sth) return $this->failed();
		$ok=$sth->execute($params ?? []);
		return new result($ok, $sth, $db);
	}
	/**
	 * 更新记录
	 * 删除记录
	 * ->update('UPDATE `cds` SET `interpret` =? WHERE `cds`.`id` = ?', ['vea', 14])
	 * @param string     $sql
	 * @param array|null $params
	 * @return result
	 */
	public function execute(string $sql, array $params=null):pdo\result{
		$this->logFormatSQL($sql, $params);
		$db=$this->db();
		$sth=$db->prepare($sql);
		if(false === $sth) return $this->failed();
		$ok=$sth->execute($params);
		return new result($ok, $sth, $db);
	}
	/**
	 * 事务
	 * @param callable $fun arg[model:$this] return ===true is rollback
	 * @return null|mixed
	 */
	public function transaction(callable $fun):?bool{
		$this->log('sql transaction begin:');
		$db=$this->db();
		$db->beginTransaction();
		$rollback=$fun($this);
		if($rollback === true){
			$db->rollBack();
			$rollback=null;
		}else $db->commit();
		$this->log('sql transaction end.');
		return $rollback;
	}
	/**
	 * 返回table对象
	 * @param string $tableName
	 * @param string $primary
	 * @return sql
	 */
	public function from(string $tableName, string $primary='id'): sql{
		return new sql($tableName, $primary, $this);
	}
}
