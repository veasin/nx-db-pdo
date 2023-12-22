<?php
declare(strict_types=1);
namespace nx\parts\db;

use Error;
use nx\helpers\db\pdo as pdo2;

trait pdo{
	protected array $db_pdo=[];//缓存
	/**
	 * @param string $name app->setup['db/pdo']
	 * @return pdo2|null
	 */
	public function db(string $name='default'):?pdo2{
		if(!array_key_exists($name, $this->db_pdo)){
			$config=($this['db/pdo'] ?? [])[$name] ?? throw new Error("db[$name] config error.");
			$this->db_pdo[$name]=new pdo2($config);
			if(method_exists($this,'runtime')) $this->db_pdo[$name]->setLog([$this, 'runtime']);
			elseif (method_exists($this, 'log')) $this->db_pdo[$name]->setLog([$this, 'log']);
		}
		return $this->db_pdo[$name];
	}
}