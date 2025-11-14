<?php

use nx\helpers\db\sql;
use nx\test\test;

include_once '../vendor/autoload.php';
error_reporting(E_ALL);

$user =sql::table('user');
$info = sql::table('info i');

//work
$sql =$user->select([123, 'id', 'name'],
	$options = [
		sql::OPTS_DISTINCT,
		sql::OPTS_HIGH_PRIORITY,
		sql::OPTS_SQL_NO_CACHE,
	]);
test::case('work', (string)$sql)
	->toBe('SELECT DISTINCT HIGH_PRIORITY SQL_NO_CACHE ?, `id`, `name` FROM `user`');

$sql->join($info, ['id' => 'id'], [])
	->join($info->as('a')->select(), ['user_id' => 'id'])
	->join($info->as('b')->select(['id', 'name']), 'id')
	->join($info->as('c')->select('c'))
	->select(null);
test::case('join clone', (string)$sql)
	->toBe('SELECT `a`.*, `b`.`id`, `b`.`name`, `c`.`c` FROM `user` LEFT JOIN `info` `i` ON (`i`.`id` = `user`.`id`) LEFT JOIN `info` `a` ON (`a`.`user_id` = `user`.`id`) LEFT JOIN `info` `b` ON (`b`.`id` = `user`.`id`) LEFT JOIN `info` `c` ON (`c`.`id` = `user`.`id`)');
//join
$user =sql::table('user');
$info = sql::table('info i');
//var_dump('user:',spl_object_id($user));
//var_dump('info:',spl_object_id($info));
$sql =$user->join($info->select(['b']), ['id', 'num' => 123]);
$sql->select();
$sql->where($info['c']->equal(567));
test::case('join', (string)$sql)
	->toBe('SELECT `user`.*, `i`.`b` FROM `user` LEFT JOIN `info` `i` ON (`i`.`id` = `user`.`id` AND `i`.`num` = ?) WHERE `i`.`c` = ?')
	->and($sql->params)
	->toBe([123, 567]);

//update
$user =sql::table('user');
$info = sql::table('info i');
$sql =$user->update(['id' => 1, 'name' => 'vea'], $options = []);
test::case('update', (string)$sql)
	->toBe('UPDATE `user` SET `id` = ?, `name` = ?')
	->and($sql->params)
	->toBe([1, 'vea']);
$sql->update(['id' => 1, 'name' => 'vea']);
test::case('update', (string)$sql)
	->toBe('UPDATE `user` SET `id` = ?, `name` = ?')
	->and($sql->params)
	->toBe([1, 'vea']);
$sql->where()->update(['id' => 1, 'name' => 'vea'])->sort(['id' => 'desc'])->limit(1);
test::case('update', (string)$sql)
	->toBe('UPDATE `user` SET `id` = ?, `name` = ? ORDER BY `id` DESC LIMIT 1')
	->and($sql->params)
	->toBe([1, 'vea']);
$sql =$info->update(['id' => 1, 'password' => $info['salt']->md5()]);
test::case('update', (string)$sql)
	->toBe('UPDATE `info` SET `id` = ?, `password` = MD5(`salt`)')
	->and($sql->params)
	->toBe([1]);
$sql->where($info['id']->equal(123))->update(['id' => 1, 'password' => $info['salt']->MD5()]);
test::case('update', (string)$sql)
	->toBe('UPDATE `info` SET `id` = ?, `password` = MD5(`salt`) WHERE `id` = ?')
	->and($sql->params)
	->toBe([1, 123]);

//insert
$user =sql::table('user');
$info = sql::table('info i');

$sql =$user->insert(['id' => 1, 'name' => 'vea'], $options = []);
test::case('insert 1', (string)$sql)
	->toBe('INSERT INTO `user` (`id`, `name`) VALUES (?, ?)')
	->and($sql->params)
	->toBe([1, 'vea']);

$sql =$info->insert([['id' => 1, 'name' => 'vea'], ['id' => 2, 'name' => 'f0']]);
test::case('insert more', (string)$sql)
	->toBe('INSERT INTO `info` (`id`, `name`) VALUES (?, ?), (?, ?)')
	->and($sql->params)
	->toBe([1, 'vea', 2, 'f0']);

//where
$user =sql::table('user');
$sql =$user->where('123')->select();
test::case('where string', (string)$sql)
	->toBe('SELECT * FROM `user` WHERE `id` = ?')
	->and($sql->params)
	->toBe(['123']);
test::case('where kv', (string)$sql->where(['id'=>1])->select())
	->toBe('SELECT * FROM `user` WHERE `id` = ?')
	->and($sql->params)
	->toBe([1]);
$info =sql::table('info i');
$sql =$info->where($info['createdAt']->TIMESTAMP($info['1'])->YEAR()->equal("3"), $info['id']->equal(4))->select();
test::case('where part', (string)$sql)
	->toBe('SELECT * FROM `info` WHERE YEAR(TIMESTAMP(`createdAt`, `1`)) = ? AND `id` = ?')
	->and($sql->params)
	->toBe(["3", 4]);

$article = sql::table('article a');
$sql =$article->where($article['status']->equal(1), $article['id']->equal(4)->or($article['id']->equal(5)))->select();
test::case('', (string)$sql)
	->toBe('SELECT * FROM `article` WHERE `status` = ? AND (`id` = ? OR `id` = ?)')
	->and($sql->params)
	->toBe([1, 4, 5]);
$article =sql::table('info a');
$sql =$article->where($article['status']->equal(1), $article['id']->equal(4)->or($article['id']->equal(5))->and($article['id']->equal(6)))->select();
test::case('', (string)$sql)
	->toBe('SELECT * FROM `info` WHERE `status` = ? AND ((`id` = ? OR `id` = ?) AND `id` = ?)')
	->and($sql->params)
	->toBe([1, 4, 5, 6]);
$article =sql::table('info2 a');
$sql=$article->where($article['status']->equal(1), $article['id']->equal(4)->or($article['id']->equal(5)->and($article['id']->equal(6))))->select();
test::case('', (string)$sql)
	->toBe('SELECT * FROM `info2` WHERE `status` = ? AND (`id` = ? OR (`id` = ? AND `id` = ?))')
	->and($sql->params)
	->toBe([1, 4, 5, 6]);

//where array
$user =sql::table('user');
$sql =$user->where(['id' => [1, 2, 3]])->select();
test::case('where in', (string)$sql)
	->toBe('SELECT * FROM `user` WHERE `id` IN (?,?,?)')
	->and($sql->params)
	->toBe([1, 2, 3]);

$sql->where(['id' => [1, 2, 3, 'fn'=>'in']])->select();
test::case('where in', (string)$sql)
	->toBe('SELECT * FROM `user` WHERE `id` IN (?, ?, ?)')
	->and($sql->params)
	->toBe([1, 2, 3]);

$sql->where(['id' => [1, 2, 3, 'fn'=>'not_in']])->select();
test::case('where not in', (string)$sql)
	->toBe('SELECT * FROM `user` WHERE `id` NOT IN (?, ?, ?)')
	->and($sql->params)
	->toBe([1, 2, 3]);

$sql->where(['id' => [1, 2, 'fn'=>'between']])->select();
test::case('where between', (string)$sql)
	->toBe('SELECT * FROM `user` WHERE `id` BETWEEN ? AND ?')
	->and($sql->params)
	->toBe([1, 2]);
//delete
$user =sql::table('user');
$sql =$user->where($user['id']->equal(1))->delete($options = []);
test::case('delete', (string)$sql)
	->toBe('DELETE FROM `user` WHERE `id` = ?')
	->and($sql->params)
	->toBe([1]);
