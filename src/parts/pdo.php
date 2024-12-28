<?php
declare(strict_types=1);
namespace nx\parts\db;

use Error;
use nx\helpers\db\pdo as nxPDO;

trait pdo{
	/**
	 * @param string $name app['db/pdo']
	 * @return nxPDO|null
	 */
	public function db(string $name='default'):?nxPDO{
		$cache_name = "db/pdo/$name";
		if(!isset($this[$cache_name])){
			$pdo =new nxPDO(($this['db/pdo'] ?? [])[$name] ?? throw new Error("db[$name] config error."));
			if(method_exists($this,'runtime')) $pdo->setLog([$this, 'runtime']);
			elseif (method_exists($this, 'log')) $pdo->setLog([$this, 'log']);
			$this[$cache_name]=$pdo;
		}
		return $this[$cache_name];
	}
}