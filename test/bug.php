<?php
use nx\helpers\db\sql;
use nx\test\test;

include_once '../vendor/autoload.php';
error_reporting(E_ALL);


$user = new sql('user');
$info = new sql('info i');
$user->join($info, ['id' => $user['id']]);
$user->where($info['id']->equal($user(1)));
$user->select();

test::case('join on', (string)$user)
	->toBe('SELECT `user`.* FROM `user` LEFT JOIN `info` `i` ON (`i`.`id` = `user`.`id`) WHERE `i`.`id` = ?')
	->and($user->params)->toBe([1]);


$user = new sql('user');
$info = new sql('info a');
$user->join($info->select(), ['user_id' => 'id'])->where([
	$user['id']->equal(1),
	$info['id']->operate($user(2), '>'),
])->select(null);

test::case('join on', (string)$user)
	->toBe('SELECT `a`.* FROM `user` LEFT JOIN `info` `a` ON (`a`.`user_id` = `user`.`id`) WHERE `user`.`id` = ? AND `a`.`id` > ?')
	->and($user->params)->toBe([1,2]);

$content =new sql('article');
$content->select(['id', 'content_id', 'url', 'title', 'desc', 'image']);
$user_course = new sql('user_course');
$user_count = $user_course::COUNT($user_course['id'])->as('count');
$content->join($user_course->select($user_count), ['content_id' => 'content_id'])->group('content_id')->sort($user_count, 'desc');
test::case('content', (string)$content)
	->toBe('SELECT `article`.`id`, `article`.`content_id`, `article`.`url`, `article`.`title`, `article`.`desc`, `article`.`image`, COUNT(`user_course`.`id`) `count` FROM `article` LEFT JOIN `user_course` ON (`user_course`.`content_id` = `article`.`content_id`) GROUP BY `article`.`content_id` ORDER BY `count` DESC');

$pp = new sql('payment_pay');
$py = new sql('payment p');
$pp->join($py, ['id' => $pp['id']]);
$pp->where($py['id']->equal($pp(1)));
$pp->select();
test::case('pay', (string)$pp)
	->toBe('SELECT `payment_pay`.* FROM `payment_pay` LEFT JOIN `payment` `p` ON (`p`.`id` = `payment_pay`.`id`) WHERE `p`.`id` = ?')
	->and($pp->params)->toBe([1]);


$table = new sql('user_c');
$article = new sql('article');
$word = new sql('word');
$user = new sql('user');
//关联关系绑定
$table->join($article->select($article['title']->as('course_name')), ['id' => 'content_id'])
	->join($word->select($word['name']->as('word_name')), ['id' => $article['word_id']])
	->join($user->select(['name', 'mobile']), ['id' => 'user_id']);
$where[] = $article['title']->operate($table("%" . 'course_name' . "%"), 'LIKE');
$where[] = $word['name']->operate($table('word_name'), '=');
$where[] = $user['name']->operate($table('nickname'), '=');
$where[] = $user['mobile']->operate($table('mobile'), '=');
$table->where($where);
$table->select();

test::case('select', (string)$table)
	->toBe('SELECT `user_c`.*, `article`.`title` `course_name`, `word`.`name` `word_name`, `user`.`name`, `user`.`mobile` FROM `user_c` LEFT JOIN `article` ON (`article`.`id` = `user_c`.`content_id`) LEFT JOIN `word` ON (`word`.`id` = `article`.`word_id`) LEFT JOIN `user` ON (`user`.`id` = `user_c`.`user_id`) WHERE `article`.`title` LIKE ? AND `word`.`name` = ? AND `user`.`name` = ? AND `user`.`mobile` = ?')
	->and($table->params)->toBe(['%course_name%', 'word_name', 'nickname', 'mobile']);


$table = new sql('user');
$table->select([
	$table::COUNT($table['xx'], true),
	$table::AVG($table['xx'], true),
	$table::MIN($table['xx'], true),
	$table::MAX($table['xx'], true),
	$table::SUM($table['xx'], true),
]);

test::case('select', (string)$table)
	->toBe('SELECT COUNT(DISTINCT `xx`), AVG(DISTINCT `xx`), MIN(DISTINCT `xx`), MAX(DISTINCT `xx`), SUM(DISTINCT `xx`) FROM `user`');


$serviceTable = new sql('service');
$table = new sql('corp_service');
$conditions['corp_id'] = 1;
$conditions['deleted_at'] = 0;
$table->where($conditions);
$table->join($serviceTable, ['id' => 'service_id', 'deleted_at' => $table(0)], ['INNER']);
$table->group($serviceTable['id']);
$serviceTable->select();
$table->select($table::COUNT('*')->as('COUNT'));
test::case('select', (string)$table)
	->toBe('SELECT COUNT(*) `COUNT`, `service`.* FROM `corp_service` INNER JOIN `service` ON (`service`.`id` = `corp_service`.`service_id` AND `service`.`deleted_at` = ?) WHERE `corp_service`.`corp_id` = ? AND `corp_service`.`deleted_at` = ? GROUP BY `service`.`id`');

$table->sort('id', 'DESC');
$table->page(1, 10);
$table->select(['corp_id', 'state_enable']);
$table->group($serviceTable['id']);
$serviceTable->select(['*']);
test::case('select', (string)$table)
	->toBe('SELECT `corp_service`.`corp_id`, `corp_service`.`state_enable`, `service`.* FROM `corp_service` INNER JOIN `service` ON (`service`.`id` = `corp_service`.`service_id` AND `service`.`deleted_at` = ?) WHERE `corp_service`.`corp_id` = ? AND `corp_service`.`deleted_at` = ? GROUP BY `service`.`id` ORDER BY `corp_service`.`id` DESC LIMIT 10');
