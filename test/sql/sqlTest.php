<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/03/26 026
 * Time: 09:33
 */

use PHPUnit\Framework\TestCase;

class sqlTest extends TestCase{
	use \nx\parts\db\pdo;

	/**
	 * @param        $name
	 * @param string $primary
	 * @param null   $db
	 * @return \nx\helpers\db\sql
	 */
	public function table($name, $primary = 'id', $db = null): \nx\helpers\db\sql{
		return new \nx\helpers\db\sql($name, $primary, $db);
	}
	public function testWork(){
		$db = new \nx\helpers\db\pdo();
		$user = $db->from('user');
		$info = $db->from('info i');
		//echo $user->where()->select();
		//$result =$sql->from($user, $info)->where()->select()->execute();
		//echo $sql->select($user['id'], $info['name'])->from($user, $info)->where($info['id']->equal(1));
		$user->select([$user(123), 'id', 'name'],
			$options = [
				$user::OPTS_DISTINCT,
				$user::OPTS_HIGH_PRIORITY,
				$user::OPTS_SQL_NO_CACHE,
			]);
		$this->assertEquals('SELECT DISTINCT HIGH_PRIORITY SQL_NO_CACHE ?, `user`.`id`, `user`.`name` FROM `user`', (string)$user);
		$user->join($info, ['id' => 'id'], [])->join($info->as('a')->select(), ['user_id' => 'id'])->join($info->as('b')->select(['id', 'name']), 'id')->join($info->as('c')->select('c'))->select(null);
		$this->assertEquals('SELECT `a`.*, `b`.`id`, `b`.`name`, `c`.`c` FROM `user` LEFT JOIN `info` `i` ON (`i`.`id` = `user`.`id`) LEFT JOIN `info` `a` ON (`a`.`user_id` = `user`.`id`) LEFT JOIN `info` `b` ON (`b`.`id` = `user`.`id`) LEFT JOIN `info` `c` ON (`c`.`id` = `user`.`id`)',
			(string)$user
		);
		//echo $info->where(1)->select([$user(123), 'id', 'name'], $options=[])->limit(1);
		//echo "\n";
		//
		//echo $user->select([1, '123', 'abc', $user['id'], $user['name']->as('n')]);
		//echo "\n";
		//
		//echo $user->where($user['id']->equal(1))->select();
		//echo "\n";
		//echo $user->where($user['createdAt']->TIMESTAMP()->YEAR()->equal(2019), $user['id']->equal(2))->select();
		//echo "\n";
		//$info =$this->table('info');
		//echo $sql->from($user, $info)->where(1)->select($user['*']->COUNT());
		//echo "\n";
		//echo $sql->limit(1,10)->select();
		//echo "\n";
		//echo $sql->page(3,10)->select();
		//echo "\n";
		//$user->collectParams =false;
		//echo $user->select()->group('id')->having($user['id']->gt(1));
		//echo "\n";
		//$user['id'];
		//$user('123')->SUBSTRING(0, 10);
		//$user::SUBSTRING('123', 0, 10);
		//$count =$user->where(1)->select($user['*']->COUNT())->execute();
		//if($count>0){
		//	$result =$sql->limit(1,10)->select();
		//}
		//return [$count, $result];
	}
	public function testJoin(){
		$db = new \nx\helpers\db\pdo();
		$user = $db->from('user');
		$info = $db->from('info i');
		$user->join($info->as('i')->select(['b']), ['id', 'num' => $user(123)]);
		$user->select();
		$user->where($info['c']->equal($user(567)));
		$this->assertEquals('SELECT `user`.*, `i`.`b` FROM `user` LEFT JOIN `info` `i` ON (`i`.`id` = `user`.`id` AND `i`.`num` = ?) WHERE `i`.`c` = ?', (string)$user);
		$this->assertEquals([123, 567], $user->params);
	}
	public function testUpdate(){
		$db = new \nx\helpers\db\pdo();
		$user = $db->from('user');
		$info = $db->from('info i');
		$user->update(['id' => 1, 'name' => 'vea'], $options = []);
		$this->assertEquals('UPDATE `user`  SET `user`.`id` = ?, `user`.`name` = ?', (string)$user);
		$this->assertEquals([1, 'vea'], $user->params);
		$user->update(['id' => 1, 'name' => 'vea']);
		$this->assertEquals('UPDATE `user`  SET `user`.`id` = ?, `user`.`name` = ?', (string)$user);
		$this->assertEquals([1, 'vea'], $user->params);
		$info->update(['id' => 1, 'password' => $info['salt']->MD5()]);
		$this->assertEquals('UPDATE `info` `i`  SET `i`.`id` = ?, `i`.`password` = MD5(`i`.`salt`)', (string)$info);
		$this->assertEquals([1], $info->params);
		$info->where($info['id']->equal(123))->update(['id' => 1, 'password' => $info['salt']->MD5()]);
		$this->assertEquals('UPDATE `info` `i`  SET `i`.`id` = ?, `i`.`password` = MD5(`i`.`salt`) WHERE `i`.`id` = ?', (string)$info);
		$this->assertEquals([1, 123], $info->params);
		$user->where()->update(['id' => 1, 'name' => 'vea'])->sort(['id' => 'desc'])->limit(1);
		$this->assertEquals('UPDATE `user`  SET `user`.`id` = ?, `user`.`name` = ? ORDER BY `user`.`id` DESC LIMIT 1', (string)$user);
		$this->assertEquals([1, 'vea'], $user->params);
	}
	public function testInsert(){
		$db = new \nx\helpers\db\pdo();
		$user = $db->from('user');
		$info = $db->from('info i');
		$user->create(['id' => 1, 'name' => 'vea'], $options = []);
		$this->assertEquals((string)$user, 'INSERT INTO `user` (`id`, `name`) VALUES (?, ?)');
		$this->assertEquals([[1, 'vea']], $user->params);
		$info->create([['id' => 1, 'name' => 'vea'], ['id' => 2, 'name' => 'f0']]);
		$this->assertEquals((string)$info, 'INSERT INTO `info` (`id`, `name`) VALUES (?, ?)');
		$this->assertEquals([[1, 'vea'], [2, 'f0']], $info->params);
	}
	public function testWhere(){
		$db = new \nx\helpers\db\pdo();
		$user = $db->from('user');
		$user->where('123')->select();
		$this->assertEquals('SELECT * FROM `user` WHERE `user`.`id` = ?', (string)$user);
		$this->assertEquals(['123'], $user->params);
		$user->where(['id' => 1])->select();
		$this->assertEquals('SELECT * FROM `user` WHERE `user`.`id` = ?', (string)$user);
		$this->assertEquals([1], $user->params);
		$info = $db->from('info i');
		$info->where($info['createdAt']->TIMESTAMP($info['1'])->YEAR()->equal("3"), $info['id']->equal(4))->select();
		$this->assertEquals('SELECT * FROM `info` `i` WHERE YEAR(TIMESTAMP(`i`.`createdAt`, `i`.`1`)) = ? AND `i`.`id` = ?', (string)$info);
		$this->assertEquals(["3", 4], $info->params);
	}
	public function testWhere2(){
		$db = new \nx\helpers\db\pdo();
		$article = $db->from('article a');
		$article->where($article['status']->equal(1), $article['id']->equal(4)->or($article['id']->equal(5)))->select();
		$this->assertEquals('SELECT * FROM `article` `a` WHERE `a`.`status` = ? AND (`a`.`id` = ? OR `a`.`id` = ?)', (string)$article);
		$this->assertEquals([1, 4, 5], $article->params);
		$article = $db->from('info a');
		$article->where($article['status']->equal(1), $article['id']->equal(4)->or($article['id']->equal(5))->and($article['id']->equal(6)))->select();
		$this->assertEquals('SELECT * FROM `info` `a` WHERE `a`.`status` = ? AND ((`a`.`id` = ? OR `a`.`id` = ?) AND `a`.`id` = ?)', (string)$article);
		$this->assertEquals([1, 4, 5, 6], $article->params);
		$article = $db->from('info2 a');
		$article->where($article['status']->equal(1), $article['id']->equal(4)->or($article['id']->equal(5)->and($article['id']->equal(6))))->select();
		$this->assertEquals('SELECT * FROM `info2` `a` WHERE `a`.`status` = ? AND (`a`.`id` = ? OR (`a`.`id` = ? AND `a`.`id` = ?))', (string)$article);
		$this->assertEquals([1, 4, 5, 6], $article->params);
	}
	public function testWhereArray(){
		$db = new \nx\helpers\db\pdo();
		$user = $db->from('user');
		$user->where(['id' => [1, 2, 3]])->select();
		$this->assertEquals('SELECT * FROM `user` WHERE `user`.`id` IN (?,?,?)', (string)$user);
		$this->assertEquals([1, 2, 3], $user->params);
		$user->where(['id' => [1, 2, 3, 'fn' => 'in']])->select();
		$this->assertEquals('SELECT * FROM `user` WHERE `user`.`id` IN (?,?,?)', (string)$user);
		$this->assertEquals([1, 2, 3], $user->params);
		$user->where(['id' => [1, 2, 3, 'fn' => 'notIn']])->select();
		$this->assertEquals('SELECT * FROM `user` WHERE `user`.`id` NOT IN (?,?,?)', (string)$user);
		$this->assertEquals([1, 2, 3], $user->params);
		$user->where(['id' => [1, 2, 'fn' => 'between']])->select();
		$this->assertEquals('SELECT * FROM `user` WHERE `user`.`id` BETWEEN ? AND ?', (string)$user);
		$this->assertEquals([1, 2], $user->params);
	}
	public function testDelete(){
		$db = new \nx\helpers\db\pdo();
		$user = $db->from('user');
		//$info =$db->from('info i');
		$user->where($user['id']->equal(1))->delete($options = []);
		$this->assertEquals('DELETE FROM `user` WHERE `user`.`id` = ?', (string)$user);
		$this->assertEquals([1], $user->params);
	}
}
/**
 * $data = Array (
 * 'login' => 'admin',
 * 'active' => true,
 * 'firstName' => 'John',
 * 'lastName' => 'Doe',
 * 'password' => $db->func('SHA1(?)',Array ("secretpassword+salt")),//todo
 * // password = SHA1('secretpassword+salt')
 * 'createdAt' => $db->now(),
 * // createdAt = NOW()
 * 'expires' => $db->now('+1Y')//todo
 * // expires = NOW() + interval 1 year
 * // Supported intervals [s]econd, [m]inute, [h]hour, [d]day, [M]onth, [Y]ear
 * );
 *
 */
//todo 1.是否要把数据库操作逻辑封装到db中再注入到$this中
