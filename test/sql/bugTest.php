<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/03/26 026
 * Time: 09:33
 */

use PHPUnit\Framework\TestCase;

class bugTest extends TestCase{
	use \nx\parts\db\pdo;

	/**
	 * @param        $name
	 * @param string $primary
	 * @param null   $db
	 * @return \nx\helpers\db\sql
	 */
	public function table($name, $primary = 'id', $db = null){
		return new \nx\helpers\db\sql($name, $primary, $db);
	}
	public function testBug1(){
		$db = new \nx\helpers\db\pdo();
		$user = $db->from('user');
		$info = $db->from('info i');
		$user->join($info, ['id' => $user['id']]);
		$user->where($info['id']->equal($user(1)));
		$user->select();
		$this->assertEquals('SELECT `user`.* FROM `user` LEFT JOIN `info` `i` ON (`i`.`id` = `user`.`id`) WHERE `i`.`id` = ?', (string)$user);
		$this->assertEquals([1], $user->params);
	}
	public function testBug2(){
		$db = new \nx\helpers\db\pdo();
		$user = $db->from('user');
		$info = $db->from('info a');
		$user->join($info->select(), ['user_id' => 'id'])->where([
				$user['id']->equal(1),
				$info['id']->operate($user(2), '>'),
			])->select(null);
		$this->assertEquals('SELECT `a`.* FROM `user` LEFT JOIN `info` `a` ON (`a`.`user_id` = `user`.`id`) WHERE `user`.`id` = ? AND `a`.`id` > ?', (string)$user);
		$this->assertEquals([1, 2], $user->params);
		//var_dump($user->params);
	}
	public function testBug3(){
		$db = new \nx\helpers\db\pdo();
		$content = $db->from('article');
		$content->select(['id', 'content_id', 'url', 'title', 'desc', 'image']);
		$user_course = $db->from('user_course');
		$user_count = $user_course::COUNT($user_course['id'])->as('count');
		$content->join($user_course->select($user_count), ['content_id' => 'content_id'])->group('content_id')->sort($user_count, 'desc');
		$this->assertEquals('SELECT `article`.`id`, `article`.`content_id`, `article`.`url`, `article`.`title`, `article`.`desc`, `article`.`image`, COUNT(`user_course`.`id`) `count` FROM `article` LEFT JOIN `user_course` ON (`user_course`.`content_id` = `article`.`content_id`) GROUP BY `article`.`content_id` ORDER BY `count` DESC',
			(string)$content
		);
	}
	public function testBug4(){
		$db = new \nx\helpers\db\pdo();
		$pp = $db->from('payment_pay');
		$py = $db->from('payment p');
		$pp->join($py, ['id' => $pp['id']]);
		$pp->where($py['id']->equal($pp(1)));
		$pp->select();
		$this->assertEquals('SELECT `payment_pay`.* FROM `payment_pay` LEFT JOIN `payment` `p` ON (`p`.`id` = `payment_pay`.`id`) WHERE `p`.`id` = ?', (string)$pp);
		$this->assertEquals([1], $pp->params);
	}
	public function testBug5(){
		$db = new \nx\helpers\db\pdo();
		$table = $db->from('user_c');
		$article = $db->from('article');
		$word = $db->from('word');
		$user = $db->from('user');
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
		$this->assertEquals('SELECT `user_c`.*, `article`.`title` `course_name`, `word`.`name` `word_name`, `user`.`name`, `user`.`mobile` FROM `user_c` LEFT JOIN `article` ON (`article`.`id` = `user_c`.`content_id`) LEFT JOIN `word` ON (`word`.`id` = `article`.`word_id`) LEFT JOIN `user` ON (`user`.`id` = `user_c`.`user_id`) WHERE `article`.`title` LIKE ? AND `word`.`name` = ? AND `user`.`name` = ? AND `user`.`mobile` = ?',
			(string)$table
		);
		$this->assertEquals(['%course_name%', 'word_name', 'nickname', 'mobile'], $table->params);
	}
	public function testBug6(){
		$db = new \nx\helpers\db\pdo();
		$table = $db->from('user');
		$table->select([
				$table::COUNT($table['xx'], true),
				$table::AVG($table['xx'], true),
				$table::MIN($table['xx'], true),
				$table::MAX($table['xx'], true),
				$table::SUM($table['xx'], true),
			]);
		$this->assertEquals('SELECT COUNT(DISTINCT `user`.`xx`), AVG(DISTINCT `user`.`xx`), MIN(DISTINCT `user`.`xx`), MAX(DISTINCT `user`.`xx`), SUM(DISTINCT `user`.`xx`) FROM `user`', (string)$table);
	}
	public function testBug7(){
		$db = new \nx\helpers\db\pdo();
		$serviceTable = $db->from('service');
		$table = $db->from('corp_service');
		$conditions['corp_id'] = 1;
		$conditions['deleted_at'] = 0;
		$table->where($conditions);
		$table->join($serviceTable, ['id' => 'service_id', 'deleted_at' => $table(0)], ['INNER']);
		$table->group($serviceTable['id']);
		$serviceTable->select();
		$table->select($table::COUNT('*')->as('COUNT'));
		$this->assertEquals('SELECT COUNT(*) `COUNT`, `service`.* FROM `corp_service` INNER JOIN `service` ON (`service`.`id` = `corp_service`.`service_id` AND `service`.`deleted_at` = ?) WHERE `corp_service`.`corp_id` = ? AND `corp_service`.`deleted_at` = ? GROUP BY `service`.`id`',
			(string)$table
		);
		$table->sort('id', 'DESC');
		$table->page(1, 10);
		$table->select(['corp_id', 'state_enable']);
		$table->group($serviceTable['id']);
		$serviceTable->select(['*']);
		$this->assertEquals('SELECT `corp_service`.`corp_id`, `corp_service`.`state_enable`, `service`.* FROM `corp_service` INNER JOIN `service` ON (`service`.`id` = `corp_service`.`service_id` AND `service`.`deleted_at` = ?) WHERE `corp_service`.`corp_id` = ? AND `corp_service`.`deleted_at` = ? GROUP BY `service`.`id` ORDER BY `corp_service`.`id` DESC LIMIT 10',
			(string)$table
		);
	}
}