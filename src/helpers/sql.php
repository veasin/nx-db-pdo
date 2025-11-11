<?php
declare(strict_types=1);
namespace nx\helpers\db;

use nx\helpers\db\pdo\result;
use nx\helpers\db\sql\part;
use nx\helpers\db\sql\part\type;

class sql implements \ArrayAccess{
	const string
		OPTS_DISTINCT = 'DISTINCT',//选项指定是否重复行应被返回。如果这些选项没有被给定，则默认值为ALL（所有的匹配行被返回）。DISTINCT和DISTINCTROW是同义词，用于指定结果集合中的重复行应被删除。
		OPTS_HIGH_PRIORITY = 'HIGH_PRIORITY', //用于促使优化符把表联合在一起，顺序按照这些表在FROM子句中排列的顺序
		OPTS_STRAIGHT_JOIN = 'STRAIGHT_JOIN', //用于促使优化符把表联合在一起，顺序按照这些表在FROM子句中排列的顺序
		OPTS_SQL_SMALL_RESULT = 'SQL_SMALL_RESULT', //可以与GROUP BY或DISTINCT同时使用，来告知优化符结果集合是较小的。在此情况下，MySAL使用快速临时表来储存生成的表，而不是使用分类。
		OPTS_SQL_BIG_RESULT = 'SQL_BIG_RESULT', //可以与GROUP BY或DISTINCT同时使用，来告知优化符结果集合有很多行。在这种情况下，MySQL直接使用以磁盘为基础的临时表（如果需要的话）。
		OPTS_SQL_BUFFER_RESULT = 'SQL_BUFFER_RESULT', //促使结果被放入一个临时表中。这可以帮助MySQL提前解开表锁定，在需要花费较长时间的情况下，也可以帮助把结果集合发送到客户端中。
		OPTS_SQL_NO_CACHE = 'SQL_NO_CACHE', //告知MySQL不要把查询结果存储在查询缓存中。
		OPTS_SQL_CALC_FOUND_ROWS = 'SQL_CALC_FOUND_ROWS';//告知MySQL计算有多少行应位于结果集合中，不考虑任何LIMIT子句。行的数目可以使用SELECT FOUND_ROWS()恢复
	const string
		JOIN_INNER = 'INNER', JOIN_CROSS = 'CROSS', JOIN_STRAIGHT = 'STRAIGHT', JOIN_LEFT = 'LEFT', JOIN_RIGHT = 'RIGHT', JOIN_NATURAL = 'NATURAL';
	public bool $collectParams = true;
	public ?sql $joinTo = null;//加入到某个sql进行联合查询
	public array $params = [];//执行参数
	//链式收集信息
	protected mixed $select = null;
	protected array $where = [], $join = [], $having = [], $set = [], $options = [];
	protected ?array $limit = null, $sort = null, $group = null;
	protected string $action = '';//最后一次操作
	protected ?string $as = null;//别名
	/**
	 * 初始化 SQL 构建器实例
	 *
	 * @param string      $table     表名（支持别名：如 "users u"）
	 * @param string      $primary   当前表的主键字段，默认为 'id'
	 * @param pdo|null    $db        数据库连接对象，用于执行查询
	 */
	public function __construct(protected string $table,//表名
		protected string $primary = 'id',//当前表的主键名
		protected ?pdo $db = null
	){
		[$this->table, $this->as] = explode(' ', "$table", 2) + ['', ''];
	}
	/**
	 * 执行当前构建的 SQL 语句
	 *
	 * @param pdo|null $db 可选的数据库连接对象，若未提供则使用构造函数传入的对象
	 * @return result 查询结果对象
	 */
	public function execute(?pdo $db = null): result{
		return ($pdo = $db ?? $this->db) ? match ($this->action) {
			'insert' => $pdo->insert((string)$this, $this->params),
			'update', 'delete' => $pdo->execute((string)$this, $this->params),
			'select' => $pdo->select((string)$this, $this->params),
			default => new result(false),
		} : new result(false);
	}
	//-------------------------------------------------------------------------------------------------------------
	private function setup(string $action, $fields, array $options): static{
		$this->action = $action;
		$this->options = $options;
		if('select' === $action) $this->select = $fields;
		else $this->set = $fields;
		return $this;
	}
	/**
	 * 插入数据（已弃用，请使用 insert 方法）
	 *
	 * @param array      $fields  要插入的字段与值 [field => value]
	 * @param array      $options 可选参数，例如 ['priority' => 'LOW_PRIORITY']
	 * @return sql 当前对象用于链式调用
	 * @deprecated 2025/06/01
	 */
	public function create(array $fields = [], array $options = []): static{
		return $this->setup('insert', $fields, $options);
	}
	/**
	 * 设置插入操作的数据
	 *
	 * @param array      $fields  要插入的字段与值 [field => value]
	 * @param array      $options 可选参数，例如 ['priority' => 'LOW_PRIORITY', 'ignore' => true]
	 * @return sql 当前对象用于链式调用
	 */
	public function insert(array $fields = [], array $options = []): static{
		return $this->setup('insert', $fields, $options);
	}
	/**
	 * 设置更新操作的数据
	 *
	 * @param array      $fields  要更新的字段与值 [field => value]
	 * @param array      $options 可选参数，例如 ['priority' => 'LOW_PRIORITY', 'ignore' => true]
	 * @return sql 当前对象用于链式调用
	 */
	public function update($fields = [], array $options = []): static{
		return $this->setup('update', $fields, $options);
	}
	/**
	 * 设置删除操作
	 *
	 * @param array      $options 可选参数，例如 ['priority' => 'LOW_PRIORITY', 'ignore' => true]
	 * @return sql 当前对象用于链式调用
	 */
	public function delete(array $options = []): static{
		return $this->setup('delete', [], $options);
	}
	/**
	 * 设置选择操作的字段
	 *
	 * @param array|string|part|null $fields  字段列表或表达式，如 ['id', 'name'] 或 '*'
	 * @param array             $options 可选参数，例如 ['distinct' => true, 'sql_no_cache' => true]
	 * @return sql 当前对象用于链式调用
	 */
	public function select(array|string|part|null $fields = [], array $options = []): static{
		return $this->setup('select', $fields, $options);
	}
	//-------------------------------------------------------------------------------------------------------------
	/**
	 * 添加 JOIN 查询子句
	 * https://dev.mysql.com/doc/refman/8.0/en/join.html
	 *
	 * @param sql         $table2 要连接的表对象
	 * @param mixed       $on     连接条件，可以是数组 ['field1' => 'field2'] 或字符串或 part 对象 USING => ['id'], ON => ['id'=>'id'], ['id'=>$user['id']] $user['id'] $user('123')
	 * @param array       $options 额外选项，如 ['LEFT', 'STRAIGHT']
	 * @return sql 当前对象用于链式调用
	 */
	public function join(sql $table2, mixed $on = null, array $options = []): static{
		$table2->joinTo = $this;
		$this->join[] = [$table2, $on ?: [$this->primary => $this->primary], $options ?: ['LEFT']];
		return $this;
	}
	//-------------------------------------------------------------------------------------------------------------
	/**
	 * 设置 WHERE 查询条件
	 *
	 * @param mixed ...$conditions 条件参数，支持多种格式：
	 *                             - 单个字符串或 part 对象；
	 *                             - 数组形式 [field => value]；
	 *                             - 嵌套数组支持复杂逻辑（如 in、between）
	 * @return sql 当前对象用于链式调用
	 */
	public function where(...$conditions): static{
		$this->where = $conditions;
		return $this;
	}
	/**
	 * 设置 LIMIT 查询范围
	 *
	 * @param int $rows   每页条数
	 * @param int $offset 偏移量，默认为 0
	 * @return sql 当前对象用于链式调用
	 */
	public function limit(int $rows, int $offset = 0): static{
		$this->limit = [$rows, $offset];
		return $this;
	}
	/**
	 * 设置分页查询（基于页码）
	 *
	 * @param int $page 当前页码（从1开始）
	 * @param int $max 每页最大数量，默认为20
	 * @return sql 当前对象用于链式调用
	 */
	public function page(int $page, int $max = 20): static{
		$this->limit = [$max, ($page - 1) * $max];
		return $this;
	}
	/**
	 * 设置排序字段及方向
	 *
	 * @param array|string|part|null $fields 排序字段，可以是单个字段或数组 ['id', 'name DESC']
	 * @param string                 $sort   排序方式，默认为 ASC（可选：ASC/DESC）
	 * @return sql 当前对象用于链式调用
	 */
	public function sort(array|string|part|null $fields = null, string $sort = 'ASC'): static{
		$this->sort = [$fields, $sort];
		return $this;
	}
	/**
	 * 设置 GROUP BY 子句
	 *
	 * @param array|string|part|null $fields 分组字段，如 ['status'] 或 ['status', 'type']
	 * @param string                 $sort   排序方式，默认为 ASC（可选：ASC/DESC）
	 * @return sql 当前对象用于链式调用
	 */
	public function group(array|string|part|null $fields = [], string $sort = 'ASC'): static{
		$this->group = [$fields, $sort];
		return $this;
	}
	/**
	 * 设置 HAVING 查询条件
	 *
	 * @param mixed ...$conditions 条件参数，格式与 where 类似
	 * @return sql 当前对象用于链式调用
	 */
	public function having(...$conditions): static{
		$this->having = $conditions;
		return $this;
	}
	/**
	 * 给当前表设置别名（返回克隆实例）
	 *
	 * @param string $name 别名名称
	 * @return sql 克隆后的对象
	 */
	public function as(string $name): static{
		$clone = clone $this;
		$clone->as = $name;
		return $clone;
	}
	/**
	 * 格式化字段名（添加反引号并处理表名）
	 *
	 * @param string|part|null $name      字段名，若为 null 则使用主键
	 * @param bool             $withTable 是否包含表名前缀，默认 true
	 * @return string 格式化后的字段名字符串
	 */
	public function formatField(string|part|null $name = null, bool $withTable = true): string{
		if($name instanceof part) return (string)$name;
		$value = $name ?? $this->primary;
		$field = ('*' === $value) ? $value : "`$value`";
		return (!$this->join && !$this->joinTo || !$withTable) ? $field : ($this->as ? "`$this->as`" : "`$this->table`") . ".$field";
	}
	/**
	 * 获取格式化的表名（含别名）
	 *
	 * @param bool $withAS 是否包含 AS 别名，默认为 true
	 * @return string 格式化后的表名字符串
	 */
	public function getFormatName(bool $withAS = true): string{
		return $withAS && $this->as ? "`$this->table` `$this->as`" : "`$this->table`";
	}
	/**
	 * 格式化值（用于占位符替换）
	 *
	 * @param mixed   $value 值
	 * @param sql|null $table 当前表对象，用于参数收集
	 * @return string 格式化后的字符串
	 */
	public static function formatValue(mixed $value, ?sql $table = null): string{
		if($value instanceof sql\part) return $table && $table->collectParams && $value->type === type::VALUE ? (string)($table->params[] = $value->value) : (string)$value;
		if($value === '*') return '*';
		if($value === '\*') return '"*"';
		if($table && $table->collectParams){
			if(is_array($value)){
				$table->params = [...$table->params, ...$value];
				return implode(',', array_fill(0, count($value), '?'));
			}
			$table->params[] = $value;
			return '?';
		}
		return match (true) {
			is_string($value) => "\"$value\"",
			is_bool($value) => $value ? 'TRUE' : 'FALSE',
			is_null($value) => 'NULL',
			is_array($value) => implode(',', $value),
			default => (string)$value,
		};
	}
	/**
	 * 转换为 SQL 字符串（自动构建完整语句）
	 *
	 * @return string 构建完成的 SQL 语句
	 */
	public function __toString(): string{
		$this->params = [];
		return match ($this->action) {
			'select' => $this->buildSelect(),
			'update' => $this->buildUpdate(),
			'delete' => $this->buildDelete(),
			'insert' => $this->buildInsert(),
			/**
			 * 13.2.3 DO Syntax
			 * DO expr [, expr] ...
			 */ default => 'DO 1',
		};
	}
	/**
	 * 13.2.2 DELETE Syntax
	 * DELETE [LOW_PRIORITY] [QUICK] [IGNORE] FROM tbl_name [[AS] tbl_alias]
	 *                [PARTITION (partition_name [, partition_name] ...)]
	 *                [WHERE where_condition]
	 *                [ORDER BY ...]
	 *                [LIMIT row_count]
	 */
	private function buildDelete(): string{
		$priority = $this->options['priority'] ?? false ? ' LOW_PRIORITY' : '';
		$ignore = $this->options['ignore'] ?? false ? ' IGNORE' : '';
		$quick = $this->options['quick'] ?? false ? ' QUICK' : '';
		$table = $this->getFormatName();
		$where = $this->buildWhere($this->where);
		$sort = $this->buildSort($this->sort);
		$limit = $this->buildLimit($this->limit);
		return "DELETE$priority$quick$ignore FROM $table$where$sort$limit";
	}
	/**
	 * 13.2.6 INSERT Syntax
	 * INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
	 *                [INTO] tbl_name
	 *                [PARTITION (partition_name [, partition_name] ...)]
	 *                [(col_name [, col_name] ...)]
	 *                {VALUES | VALUE} (value_list) [, (value_list)] ...
	 *                [ON DUPLICATE KEY UPDATE assignment_list] //todo change to this mode
	 * INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
	 *                [INTO] tbl_name
	 *                [PARTITION (partition_name [, partition_name] ...)]
	 *                SET assignment_list
	 *                [ON DUPLICATE KEY UPDATE assignment_list]
	 * INSERT [LOW_PRIORITY | HIGH_PRIORITY] [IGNORE] //todo support
	 *                [INTO] tbl_name
	 *                [PARTITION (partition_name [, partition_name] ...)]
	 *                [(col_name [, col_name] ...)]
	 *                SELECT ...
	 *                [ON DUPLICATE KEY UPDATE assignment_list]
	 * value:
	 *                {expr | DEFAULT}
	 * value_list:
	 *                value [, value] ...
	 * assignment:
	 *                col_name = value
	 * assignment_list:
	 *                assignment [, assignment] ...
	 */
	private function buildInsert(): string{
		$priority = $this->options['priority'] ?? false ? ' ' . strtoupper($this->options['priority']) : '';
		$ignore = $this->options['ignore'] ?? false ? ' IGNORE' : '';
		[$cols, $prepares, $params] = $this->buildInsertValue($this->set);
		$this->params = $params;
		$as = $this->getFormatName(false);
		return "INSERT$priority$ignore INTO $as ($cols) VALUES ($prepares)";
	}
	protected function buildInsertValue($set): array{
		if(!is_array($set) || empty($set)) return [[], [], null];
		$cols = current($set);
		if(!is_array($cols)){
			$cols = $set;
			$_set = [$set];
		}
		else $_set = $set;

		$is_named = false;
		$_cols = [];
		$_prepares = [];
		foreach($cols as $col => $value){
			$_cols[] = "`$col`";//$col instanceof sql\part ?$col :new sql\part($col, 'field', $table);
			$_prepares[] = $is_named ? ':' . $col->value : '?';
		}
		$params = [];
		foreach($_set as $index => $values){
			if(!$is_named){
				$params[$index] = array_values($values);
			}
			else{
				$kv = [];
				foreach($values as $_col => $value){
					$kv[':' . $_col] = $value;
				}
				$params[$index] = $kv;
			}
		}
		return [implode(', ', $_cols), implode(', ', $_prepares), $params];
	}
	/**
	 * 13.2.12 UPDATE Syntax
	 * UPDATE [LOW_PRIORITY] [IGNORE] table_reference
	 *                SET assignment_list
	 *                [WHERE where_condition]
	 *                [ORDER BY ...]
	 *                [LIMIT row_count]
	 * value:
	 *                {expr | DEFAULT}
	 * assignment:
	 *                col_name = value
	 * assignment_list:
	 *                assignment [, assignment] ...
	 */
	private function buildUpdate(): string{
		$priority = ($this->options['priority'] ?? false) ? ' ' . strtoupper($this->options['priority']) : '';
		$ignore = ($this->options['ignore'] ?? false) ? ' IGNORE' : '';
		$set = $this->buildSet($this->set);
		$where = $this->buildWhere($this->where);
		$table = $this->getFormatName();
		$sort = $this->buildSort($this->sort);
		$limit = $this->buildLimit($this->limit);
		return "UPDATE$priority$ignore $table $set$where$sort$limit";
	}
	/**
	 * 13.2.10 SELECT Syntax
	 * SELECT
	 *        [ALL | DISTINCT | DISTINCTROW ]    //选项指定是否重复行应被返回。如果这些选项没有被给定，则默认值为ALL（所有的匹配行被返回）。DISTINCT和DISTINCTROW是同义词，用于指定结果集合中的重复行应被删除。
	 *            [HIGH_PRIORITY]            //给予SELECT更高的优先权，高于用于更新表的语句
	 *            [STRAIGHT_JOIN]            //用于促使优化符把表联合在一起，顺序按照这些表在FROM子句中排列的顺序
	 *            [SQL_SMALL_RESULT]        //可以与GROUP BY或DISTINCT同时使用，来告知优化符结果集合是较小的。在此情况下，MySAL使用快速临时表来储存生成的表，而不是使用分类。
	 *            [SQL_BIG_RESULT]        //可以与GROUP BY或DISTINCT同时使用，来告知优化符结果集合有很多行。在这种情况下，MySQL直接使用以磁盘为基础的临时表（如果需要的话）。
	 *            [SQL_BUFFER_RESULT]        //促使结果被放入一个临时表中。这可以帮助MySQL提前解开表锁定，在需要花费较长时间的情况下，也可以帮助把结果集合发送到客户端中。
	 *            [SQL_NO_CACHE]            //告知MySQL不要把查询结果存储在查询缓存中。
	 *            [SQL_CALC_FOUND_ROWS]    //告知MySQL计算有多少行应位于结果集合中，不考虑任何LIMIT子句。行的数目可以使用SELECT FOUND_ROWS()恢复
	 *        select_expr [, select_expr ...]
	 *        [FROM table_references
	 *            [PARTITION partition_list]        //?
	 *            [WHERE where_condition]
	 *            [GROUP BY {col_name | expr | position}, ... [WITH ROLLUP]]
	 *            [HAVING where_condition]
	 *            [WINDOW window_name AS (window_spec)    //?
	 *                [, window_name AS (window_spec)] ...]
	 *            [ORDER BY {col_name | expr | position}
	 *                [ASC | DESC], ... [WITH ROLLUP]]
	 *            [LIMIT {[offset,] row_count | row_count OFFSET offset}]
	 *            [INTO OUTFILE 'file_name'
	 *                [CHARACTER SET charset_name]
	 *                export_options
	 *            | INTO DUMPFILE 'file_name'
	 *            | INTO var_name [, var_name]]
	 *            [FOR {UPDATE | SHARE} [OF tbl_name [, tbl_name] ...] [NOWAIT | SKIP LOCKED]
	 *            | LOCK IN SHARE MODE]
	 *        ]
	 *
	 * table_references:
	 *                escaped_table_reference [, escaped_table_reference] ...
	 * escaped_table_reference:
	 *                table_reference
	 *                | { OJ table_reference }
	 * table_reference:
	 *                table_factor
	 *                | joined_table
	 * table_factor:
	 *                tbl_name [PARTITION (partition_names)]
	 *                [[AS] alias] [index_hint_list]
	 *                | table_subquery [AS] alias [(col_list)]
	 *                | ( table_references )
	 * joined_table:
	 *                table_reference {[INNER | CROSS] JOIN | STRAIGHT_JOIN} table_factor [join_specification]
	 *                | table_reference {LEFT|RIGHT} [OUTER] JOIN table_reference join_specification
	 *                | table_reference NATURAL [INNER | {LEFT|RIGHT} [OUTER]] JOIN table_factor
	 * join_specification:
	 *                ON search_condition
	 *                | USING (join_column_list)
	 * join_column_list:
	 *                column_name [, column_name] ...
	 * index_hint_list:
	 *                index_hint [, index_hint] ...
	 * index_hint:
	 *                USE {INDEX|KEY}
	 *                    [FOR {JOIN|ORDER BY|GROUP BY}] ([index_list])
	 *                | {IGNORE|FORCE} {INDEX|KEY}
	 *                    [FOR {JOIN|ORDER BY|GROUP BY}] (index_list)
	 * index_list:
	 *                index_name [, index_name] ...
	 */
	private function buildSelect(): string{
		$table = $this->getFormatName();
		$select = $this->buildFields(!$this->join);
		[$join, $joinSelect] = $this->buildJoin($this->join);
		$select .= $joinSelect ? ($select ? ', ' : '') . $joinSelect : '';
		$options = $this->buildOptions([
			'DISTINCT',
			'DISTINCTROW',
			'HIGH_PRIORITY',
			'STRAIGHT_JOIN',
			'SQL_SMALL_RESULT',
			'SQL_BIG_RESULT',
			'SQL_BUFFER_RESULT',
			'SQL_NO_CACHE',
			'SQL_CALC_FOUND_ROWS',
		], $this->options);
		$where = $this->buildWhere($this->where);
		$sort = $this->buildSort($this->sort);
		$limit = $this->buildLimit($this->limit);
		$group = $this->buildSort($this->group, 'GROUP');
		$having = $this->buildWhere($this->having, ' HAVING');
		return "SELECT$options $select FROM $table$join$where$group$having$sort$limit";
	}
	private function buildJoin(array $joins): array{
		$joinStr = $select = '';
		foreach($joins as [$table2, $on, $options]){
			$opts = $this->buildOptions(['NATURAL', 'INNER', 'CROSS', 'LEFT', 'RIGHT'], $options);
			$keyword = in_array('STRAIGHT', $options) ? 'STRAIGHT_JOIN' : 'JOIN';
			if(!is_array($on)) $on = [$on => $on];
			$onStr = implode(' AND ', array_map(fn($k, $v) => "{$table2->formatField($k ?:null)} = {$this->formatField($v)}", array_keys($on), $on));
			$joinStr .= "$opts $keyword {$table2->getFormatName()} ON ($onStr)";
			$select .= ($select ? ', ' : '') . $table2->buildFields(false);
		}
		return [$joinStr, $select];
	}
	private function buildOptions(array $validOpts, array $options): string{
		$opts = array_intersect($validOpts, $options);
		return $opts ? ' ' . implode(' ', $opts) : '';
	}
	protected function buildWhere(array $where, string $command = 'WHERE'): string{
		if(!$where) return '';
		$_conditions = [];
		foreach($where as $cond){
			if(is_array($cond)){// ->where(['id'=>1, 'status'=>2, sql\part()])
				foreach($cond as $field => $value){// id => 1 , any =>sql\part()
					if($value instanceof sql\part) $_conditions[] = $value;
					elseif(is_array($value)){
						$fn = $value['fn'] ?? null;
						unset($value['fn']);
						$_conditions[] = $fn ? $this[$field]->$fn(...array_map(fn($v) => $this($v), $value)) : $this[$field]->in($value);
					}
					else $_conditions[] = $this[$field]->equal($this($value));
				}
			}
			else $_conditions[] = $cond instanceof sql\part ? $cond : $this[null]->equal($this($cond));
		}
		return !$_conditions ?"": " $command " . implode(' AND ', $_conditions);
	}
	protected function buildFields(bool $only = true): string{
		if(null === $this->select) return '';
		$select = is_array($this->select) ? $this->select : [$this->select];
		return empty($select) ? ($only ? '*' : $this->formatField('*')) : implode(', ', array_map(fn($f) => $f instanceof part ? $f : new part($f, type::FIELD, $this), $select));
	}
	protected function buildSet(array $set): string{
		if(!$set) return '';
		$params = [];
		foreach($set as $field => $value){
			$params[] = $this->formatField($field) . ' = ' . self::formatValue($value, $this);
		}
		return ' SET ' . implode(', ', $params);
	}
	protected function buildSort(?array $sort, string $command = "ORDER"): string{
		if(empty($sort)) return '';
		[$field, $asc] = $sort;
		$sorts = [];
		if(is_array($field)){
			foreach($field as $f => $s){
				$fieldName = is_string($f) ? $this->formatField($f) : (is_numeric($f) ? $this->formatField($s) : (string)$f);
				$direction = is_bool($s) || is_string($s) ? (strtoupper($s[0] ?? 'A') === 'A' ? 'ASC' : 'DESC') : ($asc ? 'ASC' : 'DESC');
				$sorts[] = 'GROUP' === $command ? $fieldName : "$fieldName $direction";
			}
		}
		elseif($field instanceof part || is_string($field)){
			$fieldName = $field instanceof part ? ($field->as ? "`{$field->as}`" : (string)$field) : $this->formatField($field);
			$direction = is_bool($asc) || is_string($asc) ? (strtoupper($asc[0] ?? 'A') === 'A' ? 'ASC' : 'DESC') : 'ASC';
			$sorts[] = 'GROUP' === $command ? $fieldName : "$fieldName $direction";
		}
		return empty($sorts) ? '' : " $command BY " . implode(", ", $sorts);
	}
	protected function buildLimit(?array $limit): string{
		return $limit ? ($limit[1] ? " LIMIT $limit[1], $limit[0]" : " LIMIT $limit[0]") : '';
	}
	/**
	 * 将值包装成 part 对象，用于表达式构造
	 *
	 * @param mixed $value 值（支持字符串、数字、布尔等）
	 * @return part part 对象
	 */
	public function __invoke(mixed $value): part{
		return new part($value, type::VALUE, $this);
	}
	/**
	 * 静态方法调用，创建函数类型的 part 对象
	 *
	 * @param string $name      函数名（如 'COUNT', 'SUM'）
	 * @param array  $arguments 参数列表
	 * @return part part 对象
	 */
	public static function __callStatic($name, $arguments): part{
		return new part($name, type::FUNCTION)->arguments(...$arguments);
	}
	public function offsetSet($offset, $value): void{}
	public function offsetExists($offset): bool{ return false; }
	public function offsetUnset($offset): void{}
	/**
	 * 实现 ArrayAccess 接口，用于获取字段对应的 part 对象
	 *
	 * @param string|part|null $offset 字段名或表达式
	 * @return part part 对象
	 */
	public function offsetGet(mixed $offset): part{
		return new part($offset, type::FIELD, $this);
	}
	/**
	 * 提供调试信息，返回对象内部状态
	 *
	 * @return array 包含当前 SQL 构建器的信息
	 */
	public function __debugInfo(): array{
		return [
			'table' => "$this->table $this->as",
			'action'=>$this->action,
			'params'=>$this->params,
			'select'=>$this->select,
			'where'=>$this->where,
			'join'=>$this->join,
			'limit'=>$this->limit,
			'sort'=>$this->sort,
			'group'=>$this->group,
		];
	}
}