<?php

use nx\helpers\db\sql;
use nx\test\test;

include_once '../vendor/autoload.php';
error_reporting(E_ALL);

$user =new sql('user');
$info = new sql('info i');

//work
$user->select([$user(123), 'id', 'name'],
	$options = [
		$user::OPTS_DISTINCT,
		$user::OPTS_HIGH_PRIORITY,
		$user::OPTS_SQL_NO_CACHE,
	]);
test::case('work', (string)$user)
	->toBe('SELECT DISTINCT HIGH_PRIORITY SQL_NO_CACHE ?, `id`, `name` FROM `user`');

$user->join($info, ['id' => 'id'], [])
	->join($info->as('a')->select(), ['user_id' => 'id'])
	->join($info->as('b')->select(['id', 'name']), 'id')
	->join($info->as('c')->select('c'))
	->select(null);
test::case('join clone', (string)$user)
	->toBe('SELECT `a`.*, `b`.`id`, `b`.`name`, `c`.`c` FROM `user` LEFT JOIN `info` `i` ON (`i`.`id` = `user`.`id`) LEFT JOIN `info` `a` ON (`a`.`user_id` = `user`.`id`) LEFT JOIN `info` `b` ON (`b`.`id` = `user`.`id`) LEFT JOIN `info` `c` ON (`c`.`id` = `user`.`id`)');
//join
$user =new sql('user');
$info = new sql('info i');
//var_dump('user:',spl_object_id($user));
//var_dump('info:',spl_object_id($info));
$user->join($info->select(['b']), ['id', 'num' => $user(123)]);
$user->select();
$user->where($info['c']->equal($user(567)));
test::case('join', (string)$user)
	->toBe('SELECT `user`.*, `i`.`b` FROM `user` LEFT JOIN `info` `i` ON (`i`.`id` = `user`.`id` AND `i`.`num` = ?) WHERE `i`.`c` = ?')
	->and($user->params)
	->toBe([123, 567]);

//update
$user =new sql('user');
$info = new sql('info i');
$user->update(['id' => 1, 'name' => 'vea'], $options = []);
test::case('update', (string)$user)
	->toBe('UPDATE `user`  SET `id` = ?, `name` = ?')
	->and($user->params)
	->toBe([1, 'vea']);
$user->update(['id' => 1, 'name' => 'vea']);
test::case('update', (string)$user)
	->toBe('UPDATE `user`  SET `id` = ?, `name` = ?')
	->and($user->params)
	->toBe([1, 'vea']);
$user->where()->update(['id' => 1, 'name' => 'vea'])->sort(['id' => 'desc'])->limit(1);
test::case('update', (string)$user)
	->toBe('UPDATE `user`  SET `id` = ?, `name` = ? ORDER BY `id` DESC LIMIT 1')
	->and($user->params)
	->toBe([1, 'vea']);
$info->update(['id' => 1, 'password' => $info['salt']->md5()]);
test::case('update', (string)$info)
	->toBe('UPDATE `info` `i`  SET `id` = ?, `password` = MD5(`salt`)')
	->and($info->params)
	->toBe([1]);
$info->where($info['id']->equal(123))->update(['id' => 1, 'password' => $info['salt']->MD5()]);
test::case('update', (string)$info)
	->toBe('UPDATE `info` `i`  SET `id` = ?, `password` = MD5(`salt`) WHERE `id` = ?')
	->and($info->params)
	->toBe([1, 123]);

//insert
$user =new sql('user');
$info = new sql('info i');

$user->create(['id' => 1, 'name' => 'vea'], $options = []);
test::case('insert 1', (string)$user)
	->toBe('INSERT INTO `user` (`id`, `name`) VALUES (?, ?)')
	->and($user->params)
	->toBe([[1, 'vea']]);

$info->create([['id' => 1, 'name' => 'vea'], ['id' => 2, 'name' => 'f0']]);
test::case('insert more', (string)$info)
	->toBe('INSERT INTO `info` (`id`, `name`) VALUES (?, ?)')
	->and($info->params)
	->toBe([[1, 'vea'], [2, 'f0']]);

//where
$user =new sql('user');
test::case('where string', (string)$user->where('123')->select())
	->toBe('SELECT * FROM `user` WHERE `id` = ?')
	->and($user->params)
	->toBe(['123']);
test::case('where kv', (string)$user->where(['id'=>1])->select())
	->toBe('SELECT * FROM `user` WHERE `id` = ?')
	->and($user->params)
	->toBe([1]);
$info =new sql('info i');
test::case('where part', (string)$info->where($info['createdAt']->TIMESTAMP($info['1'])->YEAR()->equal("3"), $info['id']->equal(4))->select())
	->toBe('SELECT * FROM `info` `i` WHERE YEAR(TIMESTAMP(`createdAt`, `1`)) = ? AND `id` = ?')
	->and($info->params)
	->toBe(["3", 4]);

$article = new sql('article a');
$article->where($article['status']->equal(1), $article['id']->equal(4)->or($article['id']->equal(5)))->select();
test::case('', (string)$article)
	->toBe('SELECT * FROM `article` `a` WHERE `status` = ? AND (`id` = ? OR `id` = ?)')
	->and($article->params)
	->toBe([1, 4, 5]);
$article =new sql('info a');
$article->where($article['status']->equal(1), $article['id']->equal(4)->or($article['id']->equal(5))->and($article['id']->equal(6)))->select();
test::case('', (string)$article)
	->toBe('SELECT * FROM `info` `a` WHERE `status` = ? AND ((`id` = ? OR `id` = ?) AND `id` = ?)')
	->and($article->params)
	->toBe([1, 4, 5, 6]);
$article =new sql('info2 a');
$article->where($article['status']->equal(1), $article['id']->equal(4)->or($article['id']->equal(5)->and($article['id']->equal(6))))->select();
test::case('', (string)$article)
	->toBe('SELECT * FROM `info2` `a` WHERE `status` = ? AND (`id` = ? OR (`id` = ? AND `id` = ?))')
	->and($article->params)
	->toBe([1, 4, 5, 6]);

//where array
$user =new sql('user');
$user->where(['id' => [1, 2, 3]])->select();
test::case('where in', (string)$user)
	->toBe('SELECT * FROM `user` WHERE `id` IN (?,?,?)')
	->and($user->params)
	->toBe([1, 2, 3]);

$user->where(['id' => [1, 2, 3, 'fn'=>'in']])->select();
test::case('where in', (string)$user)
	->toBe('SELECT * FROM `user` WHERE `id` IN (?,?,?)')
	->and($user->params)
	->toBe([1, 2, 3]);

$user->where(['id' => [1, 2, 3, 'fn'=>'notIn']])->select();
test::case('where not in', (string)$user)
	->toBe('SELECT * FROM `user` WHERE `id` NOT IN (?,?,?)')
	->and($user->params)
	->toBe([1, 2, 3]);

$user->where(['id' => [1, 2, 'fn'=>'between']])->select();
test::case('where between', (string)$user)
	->toBe('SELECT * FROM `user` WHERE `id` BETWEEN ? AND ?')
	->and($user->params)
	->toBe([1, 2]);
//delete
$user =new sql('user');
$user->where($user['id']->equal(1))->delete($options = []);
test::case('delete', (string)$user)
	->toBe('DELETE FROM `user` WHERE `id` = ?')
	->and($user->params)
	->toBe([1]);
