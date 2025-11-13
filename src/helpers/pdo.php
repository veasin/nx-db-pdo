<?php
declare(strict_types=1);

namespace nx\helpers\db;

use nx\helpers\db\pdo\result;
use nx\helpers\db\sql\table;

class pdo{
	private array $_nx_db_pdo_options = [
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_STRINGIFY_FETCHES => false,
		\PDO::ATTR_EMULATE_PREPARES => false,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
	];
	private ?\PDO $link = null;
	private $logging;
	public function __construct(private array $setup = []){
		$this->setup['options'] = ($this->setup['options'] ?? []) + $this->_nx_db_pdo_options;
		$this->logging = !!\nx\app::$instance;
	}
	private function isConnected(): bool{
		if(!$this->link) return false;
		try{
			if($this->link->getAttribute(\PDO::ATTR_CONNECTION_STATUS) !== false) return true;
			if(method_exists($this->link, 'ping')) return $this->link->ping();
			$this->link->query('SELECT 1');
			return true;
		}catch(\PDOException){
			return false;
		}
	}
	private function db(): \PDO{
		if(!$this->isConnected()) $this->link = new \PDO($this->setup['dsn'], $this->setup['username'], $this->setup['password'], $this->setup['options']);
		return $this->link;
	}
	private function log(string $template, array $data = []): void{
		if(!$this->logging) return;
		\nx\app::$instance->log($template, 'sql');
		$data && \nx\app::$instance->log($data, 'data');
	}
	private function logFormatSQL(string $prepare, ?array $params = null, string $action = ''): void{
		if(!$this->logging) return;
		$params ??= [];
		$sql = str_replace('?', '%s', $prepare);
		$map = fn($v) => match (true) {
			is_null($v) => 'NULL',
			is_int($v) || is_float($v) => $v,
			default => "'$v'",
		};
		if($action === 'insert' && (is_array($params[0] ?? null) || ($params[0] ?? null) instanceof \Traversable)){
			foreach($params as $p) $this->log(' ' . sprintf($sql, ...array_map($map, $p)));
		}
		else $this->log(' ' . sprintf($sql, ...array_map($map, $params)));
	}
	private function failed(): result{
		$this->log('sql error: %s %s %s', $this->link->errorInfo());
		return new result(false, null, $this->db());
	}
	private function result(string $sql, ?array $params, string $action): result{
		$this->logFormatSQL($sql, $params, $action);
		$db = $this->db();
		try{
			if(empty($params)){
				$stmt = $db->query($sql);
				return new result($stmt !== false, $stmt, $db);
			}
			$stmt = $db->prepare($sql);
			if(!$stmt) return $this->failed();
			$success = is_array($params[0])
				? array_reduce($params,fn(bool $carry, array $p) => $carry && $stmt->execute($p),true)
				: $stmt->execute($params);
			return new result((bool)$success, $stmt, $db);
		}catch(\PDOException){
			return $this->failed();
		}
	}
	public function insert(string $sql, ?array $params = null): result{ return $this->result($sql, $params, 'insert'); }
	public function select(string $sql, ?array $params = null): result{ return $this->result($sql, $params, 'select'); }
	public function execute(string $sql, ?array $params = null): result{ return $this->result($sql, $params, 'update'); }
	/**
	 * 事务
	 * @param callable(pdo): ?bool $func 回调函数返回 true 则回滚事务
	 * @return bool|null
	 */
	public function transaction(callable $func): ?bool{
		$this->log('sql transaction begin:');
		$db = $this->db();
		$db->beginTransaction();
		try{
			$rollback = (bool)$func($this);
			$rollback ? $db->rollBack() : $db->commit();
			$this->log('sql transaction end.');
			return $rollback;
		}catch(\Exception $e){
			$db->rollBack();
			$this->log('sql transaction rollback: ' . $e->getMessage());
			return null;
		}
	}
	public function table(string $name, string $primary = 'id'): table{ return sql::table($name, $primary, $this); }
	/**
	 * @deprecated 2025/11/13
	 */
	public function from(string $name, string $primary = 'id'): table{ return sql::table($name, $primary, $this); }
}