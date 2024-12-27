<?php
namespace nx\parts\db;

use nx\helpers\db\pdo;
use nx\helpers\db\sql;

/**
 * @method pdo db(string $param)
 */
trait table{
	protected string $tableName='';
	protected string $dbConfig='default';
	protected string $tablePrimary='id';
	/**
	 * @param string|null $tableName
	 * @param string|null $primary
	 * @param string|null $config
	 * @return sql
	 */
	protected function table(?string $tableName=null, ?string $primary=null, ?string $config=null):sql{
		return $this->db($config ?? $this->dbConfig)->from($tableName ?? $this->tableName, $primary ?? $this->tablePrimary ?? 'id');
	}
}
