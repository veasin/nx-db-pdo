<?php
declare(strict_types=1);

namespace nx\helpers\db;

use Closure;
use nx\helpers\db\pdo\result;

class pdo{
	private array $_nx_db_pdo_options = [
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_STRINGIFY_FETCHES => false,
		\PDO::ATTR_EMULATE_PREPARES => false,
	];
	private int $timeout;
	private ?\PDO $link = null;
	//private array $setup;
	private ?Closure $_log = null;
	public function __construct(private array $setup = []){
		$this->setup['options'] = ($this->setup['options'] ?? []) + $this->_nx_db_pdo_options;
		$this->timeout = $this->setup['timeout'] ?? 0;
	}
	public function setLog(callable $logger): void{
		$this->_log = $logger(...);
	}
	private function db(): \PDO{
		$now = time();
		if(!$this->link || ($this->timeout > 0 && $this->timeout < $now)){
			$this->link = new \PDO($this->setup['dsn'], $this->setup['username'], $this->setup['password'], $this->setup['options']);
			$this->timeout = ($this->setup['timeout'] ?? 0) > 0 ? $now + $this->setup['timeout'] : 0;
		}
		return $this->link;
	}
	private function log(string $template, array $data = []): void{
		$this->_log?->__invoke($template, 'sql');
		if($data) $this->_log?->__invoke($data, 'data');
	}
	private function logFormatSQL(string $prepare, ?array $params = null, string $action = ''): void{
		$params ??= [];
		$sql = str_replace('?', '%s', $prepare);
		$map = fn($v) => is_int($v) ? $v : "\"$v\"";
		if($action === 'insert' && (is_array($params[0] ?? null) || ($params[0] ?? null) instanceof \Traversable)){
			foreach($params as $p){
				$this->log(' ' . sprintf($sql, ...array_map($map, $p)));
			}
			return;
		}
		$this->log(' ' . sprintf($sql, ...array_map($map, $params)));
	}
	private function failed(): result{
		$this->log('sql error: %s %s %s', $this->link->errorInfo());
		return new result(false, null, $this->db());
	}
	private function prepareAndExecute(string $sql, ?array $params, string $action): result{
		$this->logFormatSQL($sql, $params, $action);
		$db = $this->db();
		try{
			if(empty($params)){
				$stmt = $db->query($sql);
				return new result($stmt !== false, $stmt, $db);
			}
			$stmt = $db->prepare($sql);
			if(!$stmt) return $this->failed();
			$isMulti = isset($params[0]) && is_array($params[0]);
			$success = $isMulti ? array_product(array_map(fn($p) => $stmt->execute($p), $params)) : $stmt->execute($params);
			return new result((bool)$success, $stmt, $db);
		}catch(\PDOException $e){
			return $this->failed();
		}
	}
	public function insert(string $sql, ?array $params = null): result{ return $this->prepareAndExecute($sql, $params, 'insert'); }
	public function select(string $sql, ?array $params = null): result{ return $this->prepareAndExecute($sql, $params, 'select'); }
	public function execute(string $sql, ?array $params = null): result{ return $this->prepareAndExecute($sql, $params, 'update'); }
	public function transaction(callable $func): ?bool{
		$this->log('sql transaction begin:');
		$db = $this->db();
		$db->beginTransaction();
		try{
			$rollback = $func($this);
			$rollback ? $db->rollBack() : $db->commit();
			$this->log('sql transaction end.');
			return $rollback;
		}catch(\Exception $e){
			$db->rollBack();
			$this->log('sql transaction rollback: ' . $e->getMessage());
			return null;
		}
	}
	public function from(string $tableName, string $primary = 'id'): sql{ return new sql($tableName, $primary, $this); }
}