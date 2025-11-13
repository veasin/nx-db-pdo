<?php
declare(strict_types=1);
namespace nx\helpers\db;

use nx\helpers\db\pdo\result;
use nx\helpers\db\sql\expr;
use nx\helpers\db\sql\operate;
use nx\helpers\db\sql\table;
use nx\helpers\db\sql\value;

/**
 * @method static operate ABS(float|int $expr) - ABS(expr) 返回绝对值
 * @method static operate ACOS(float $expr) - ACOS(expr) 返回反余弦值
 * @method static operate ADDDATE(string $date, string $interval) - ADDDATE(date, interval) 将时间间隔加到日期上
 * @method static operate ADDTIME(string $time, string $interval) - ADDTIME(time, interval) 将时间间隔加到时间上
 * @method static operate AES_DECRYPT(string $data, string $key) - AES_DECRYPT(data, key) 使用AES解密数据
 * @method static operate AES_ENCRYPT(string $data, string $key) - AES_ENCRYPT(data, key) 使用AES加密数据
 * @method static operate ANY_VALUE(mixed $expr) - ANY_VALUE(expr) 抑制 ONLY_FULL_GROUP_BY 错误
 * @method static operate ASCII(string $str) - ASCII(str) 返回字符串最左字符的ASCII码
 * @method static operate ASIN(float $expr) - ASIN(expr) 返回反正弦值
 * @method static operate ATAN(float $expr) - ATAN(expr) 返回反正切值
 * @method static operate ATAN2(float $y, float $x) - ATAN2(y, x) 返回两参数的反正切值
 * @method static operate AVG(float $expr) - AVG(expr) 返回平均值
 * @method static operate BENCHMARK(int $n, mixed $expr) - BENCHMARK(n, expr) 重复执行表达式
 * @method static operate BIN(int $num) - BIN(num) 返回数字的二进制字符串表示
 * @method static operate BIN_TO_UUID(string $bin) - BIN_TO_UUID(bin) 将二进制UUID转换为字符串
 * @method static operate BINARY(string $str) - BINARY(str) 将字符串转换为二进制字符串
 * @method static operate BIT_AND(array $expr_list) - BIT_AND(expr_list) 返回按位与结果
 * @method static operate BIT_COUNT(int $expr) - BIT_COUNT(expr) 返回设置的位数
 * @method static operate BIT_LENGTH(string $expr) - BIT_LENGTH(expr) 返回参数的位数
 * @method static operate BIT_OR(array $expr_list) - BIT_OR(expr_list) 返回按位或结果
 * @method static operate BIT_XOR(array $expr_list) - BIT_XOR(expr_list) 返回按位异或结果
 * @method static operate CAST(mixed $expr, string $type) - CAST(expr AS type) 将值转换为指定类型
 * @method static operate CEIL(float $expr) - CEIL(expr) 返回不小于该值的最小整数
 * @method static operate CEILING(float $expr) - CEILING(expr) 返回不小于该值的最小整数
 * @method static operate CHAR(int $num) - CHAR(num) 返回指定整数对应的字符
 * @method static operate CHAR_LENGTH(string $str) - CHAR_LENGTH(str) 返回字符串字符数
 * @method static operate CHARACTER_LENGTH(string $str) - CHARACTER_LENGTH(str) 返回字符串字符数（CHAR_LENGTH的别名）
 * @method static operate CHARSET(string $str) - CHARSET(str) 返回字符串字符集
 * @method static operate COALESCE(mixed ...$expr) - COALESCE(expr1, expr2, ...) 返回第一个非NULL值
 * @method static operate COERCIBILITY(string $str) - COERCIBILITY(str) 返回字符串的排序强制值
 * @method static operate COLLATION(string $str) - COLLATION(str) 返回字符串的排序规则
 * @method static operate COMPRESS(string $str) - COMPRESS(str) 返回压缩后的二进制字符串
 * @method static operate CONCAT(string ...$str) - CONCAT(str1, str2, ...) 返回拼接字符串
 * @method static operate CONCAT_WS(string $separator, string ...$str) - CONCAT_WS(separator, str1, str2, ...) 返回拼接字符串，中间用分隔符
 * @method static operate CONNECTION_ID() - CONNECTION_ID() 返回当前连接ID
 * @method static operate CONV(int $num, int $from_base, int $to_base) - CONV(num, from_base, to_base) 在不同进制间转换数字
 * @method static operate CONVERT(mixed $expr, string $type) - CONVERT(expr, type) 将值转换为指定类型
 * @method static operate CONVERT_TZ(string $datetime, string $from_tz, string $to_tz) - CONVERT_TZ(datetime, from_tz, to_tz) 转换时区
 * @method static operate COS(float $expr) - COS(expr) 返回余弦值
 * @method static operate COT(float $expr) - COT(expr) 返回余切值
 * @method static operate COUNT(mixed $expr) - COUNT(expr) 返回符合条件的行数
 //* @method static operate COUNT(DISTINCT mixed $expr) - COUNT(DISTINCT expr) 返回不同值的行数
 * @method static operate CRC32(string $expr) - CRC32(expr) 计算循环冗余校验值
 * @method static operate CUME_DIST() - CUME_DIST() 返回累积分布值
 * @method static operate CURDATE() - CURDATE() 返回当前日期
 * @method static operate CURRENT_DATE() - CURRENT_DATE() 返回当前日期
 * @method static operate CURRENT_ROLE() - CURRENT_ROLE() 返回当前活动角色
 * @method static operate CURRENT_TIME() - CURRENT_TIME() 返回当前时间
 * @method static operate CURRENT_TIMESTAMP() - CURRENT_TIMESTAMP() 返回当前日期和时间
 * @method static operate CURRENT_USER() - CURRENT_USER() 返回当前用户名称和主机名
 * @method static operate CURTIME() - CURTIME() 返回当前时间
 * @method static operate DATABASE() - DATABASE() 返回当前数据库名
 * @method static operate DATE(string $date) - DATE(date) 提取日期部分
 * @method static operate DATE_ADD(string $date, string $interval) - DATE_ADD(date, interval) 将时间间隔加到日期上
 * @method static operate DATE_FORMAT(string $date, string $format) - DATE_FORMAT(date, format) 格式化日期
 * @method static operate DATE_SUB(string $date, string $interval) - DATE_SUB(date, interval) 从日期减去时间间隔
 * @method static operate DATEDIFF(string $date1, string $date2) - DATEDIFF(date1, date2) 计算两个日期差值
 * @method static operate DAY(string $date) - DAY(date) 返回日期的天数（0-31）
 * @method static operate DAYNAME(string $date) - DAYNAME(date) 返回星期名称
 * @method static operate DAYOFMONTH(string $date) - DAYOFMONTH(date) 返回日期的月内天数（0-31）
 * @method static operate DAYOFWEEK(string $date) - DAYOFWEEK(date) 返回星期索引（0-6）
 * @method static operate DAYOFYEAR(string $date) - DAYOFYEAR(date) 返回日期的年中天数（1-366）
 * @method static operate DEFAULT(string $col) - DEFAULT(col) 返回表列的默认值
 * @method static operate DEGREES(float $radians) - DEGREES(radians) 将弧度转换为角度
 * @method static operate DENSE_RANK() - DENSE_RANK() 返回分区内的排名（无间隙）
 * @method static operate DIV(int $expr1, int $expr2) - DIV(expr1 DIV expr2) 整数除法
 * @method static operate ELT(int $index, string ...$str) - ELT(index, str1, str2, ...) 返回指定索引的字符串
 * @method static operate EXP(float $expr) - EXP(expr) 返回指数值（e^expr）
 * @method static operate EXPORT_SET(int $bits, string $on_str, string $off_str, int $default_on, int $default_off) - EXPORT_SET(bits, on_str, off_str, default_on, default_off) 返回设置位的字符串
 * @method static operate EXTRACT(string $date_part, string $date) - EXTRACT(date_part FROM date) 提取日期部分
 * @method static operate FIELD(string $str, string ...$str_list) - FIELD(str, str1, str2, ...) 返回字符串在列表中的位置
 * @method static operate FIND_IN_SET(string $str, string $str_list) - FIND_IN_SET(str, str_list) 返回字符串在列表中的位置
 * @method static operate FIRST_VALUE(mixed $expr) - FIRST_VALUE(expr) 返回窗口帧中第一行的值
 * @method static operate FLOOR(float $expr) - FLOOR(expr) 返回不大于该值的最大整数
 * @method static operate FORMAT(float $num, int $dec_places) - FORMAT(num, dec_places) 格式化数字为指定小数位数
 * @method static operate FORMAT_BYTES(int $bytes) - FORMAT_BYTES(bytes) 将字节数转换为带单位的值
 * @method static operate FORMAT_PICO_TIME(int $pico_seconds) - FORMAT_PICO_TIME(pico_seconds) 将皮秒转换为带单位的值
 * @method static operate FOUND_ROWS() - FOUND_ROWS() 返回LIMIT前的行数
 * @method static operate FROM_BASE64(string $str) - FROM_BASE64(str) 解码Base64字符串
 * @method static operate FROM_DAYS(int $days) - FROM_DAYS(days) 将天数转换为日期
 * @method static operate FROM_UNIXTIME(int $timestamp) - FROM_UNIXTIME(timestamp) 将Unix时间戳格式化为日期
 * @method static operate GET_FORMAT() - GET_FORMAT() 返回日期格式字符串
 * @method static operate GET_LOCK(string $lock_name, int $timeout) - GET_LOCK(lock_name, timeout) 获取命名锁
 * @method static operate GREATEST(mixed ...$expr) - GREATEST(expr1, expr2, ...) 返回最大值
 * @method static operate GROUP_CONCAT(mixed $expr) - GROUP_CONCAT(expr) 返回拼接字符串
 * @method static operate GROUPING() - GROUPING() 区分聚合行与普通行
 * @method static operate HEX(int|string $num) - HEX(num) 返回十进制或字符串的十六进制表示
 * @method static operate HOUR(string $time) - HOUR(time) 提取小时
 * @method static operate IF(bool $condition, mixed $true_value, mixed $false_value) - IF(condition, true_value, false_value) 条件判断
 * @method static operate IFNULL(mixed $expr1, mixed $expr2) - IFNULL(expr1, expr2) NULL判断
 * @method static operate INET_ATON(string $ip) - INET_ATON(ip) 返回IP地址的数值
 * @method static operate INET_NTOA(int $num) - INET_NTOA(num) 返回IP地址
 * @method static operate INSERT(string $str, int $pos, int $len) - INSERT(str, pos, len) 在指定位置插入子串
 * @method static operate INSTR(string $str, string $substring) - INSTR(str, substring) 返回子串首次出现位置
 * @method static operate INTERVAL(float $expr, string $unit) - INTERVAL(expr, unit) 返回小于第一个参数的索引
 * @method static operate IS_FREE_LOCK(string $lock_name) - IS FREE_LOCK(lock_name) 判断锁是否空闲
 * @method static operate IS_USED_LOCK(string $lock_name) - IS_USED_LOCK(lock_name) 判断锁是否被使用
 * @method static operate IS_UUID(string $expr) - IS_UUID(expr) 判断是否为有效UUID
 * @method static operate ISNULL(mixed $expr) - ISNULL(expr) 判断是否为NULL
 * @method static operate JSON_ARRAY() - JSON_ARRAY() 创建JSON数组
 * @method static operate JSON_ARRAY_APPEND(string $json_doc, string $path, mixed $value) - JSON_ARRAY_APPEND(json_doc, path, value) 在JSON文档中追加数据
 * @method static operate JSON_ARRAY_INSERT(string $json_doc, string $path, mixed $value) - JSON_ARRAY_INSERT(json_doc, path, value) 在JSON数组中插入数据
 * @method static operate JSON_ARRAYAGG() - JSON_ARRAYAGG() 返回结果集为单个JSON数组
 * @method static operate JSON_CONTAINS(string $json_doc, string $path) - JSON_CONTAINS(json_doc, path) 判断JSON文档是否包含指定路径
 * @method static operate JSON_CONTAINS_PATH(string $json_doc, string $path) - JSON_CONTAINS_PATH(json_doc, path) 判断JSON文档是否包含指定路径
 * @method static operate JSON_DEPTH(string $json_doc) - JSON_DEPTH(json_doc) 返回JSON文档最大深度
 * @method static operate JSON_EXTRACT(string $json_doc, string $path) - JSON_EXTRACT(json_doc, path) 从JSON文档提取数据
 * @method static operate JSON_INSERT(string $json_doc, string $path, mixed $value) - JSON_INSERT(json_doc, path, value) 在JSON文档中插入数据
 * @method static operate JSON_KEYS(string $json_doc) - JSON_KEYS(json_doc) 返回JSON文档的键列表
 * @method static operate JSON_LENGTH(string $json_doc) - JSON_LENGTH(json_doc) 返回JSON文档元素数
 * @method static operate JSON_MERGE(string $json_doc1, string $json_doc2) - JSON_MERGE(json_doc1, json_doc2) 合并JSON文档（保留重复键）
 * @method static operate JSON_MERGE_PATCH(string $json_doc1, string $json_doc2) - JSON_MERGE_PATCH(json_doc1, json_doc2) 合并JSON文档（覆盖重复键）
 * @method static operate JSON_MERGE_PRESERVE(string $json_doc1, string $json_doc2) - JSON_MERGE_PRESERVE(json_doc1, json_doc2) 合并JSON文档（保留重复键）
 * @method static operate JSON_OBJECT() - JSON_OBJECT() 创建JSON对象
 * @method static operate JSON_OBJECTAGG() - JSON_OBJECTAGG() 返回结果集为单个JSON对象
 * @method static operate JSON_OVERLAPS(string $json_doc1, string $json_doc2) - JSON_OVERLAPS(json_doc1, json_doc2) 判断两个JSON文档是否有共同键值或数组元素
 * @method static operate JSON_PRETTY(string $json_doc) - JSON_PRETTY(json_doc) 以可读格式打印JSON文档
 * @method static operate JSON_QUOTE(string $json_doc) - JSON_QUOTE(json_doc) 对JSON文档进行转义
 * @method static operate JSON_REMOVE(string $json_doc, string $path) - JSON_REMOVE(json_doc, path) 从JSON文档中删除数据
 * @method static operate JSON_REPLACE(string $json_doc, string $path, mixed $value) - JSON_REPLACE(json_doc, path, value) 替换JSON文档中的值
 * @method static operate JSON_SCHEMA_VALID(string $json_doc, string $schema) - JSON_SCHEMA_VALID(json_doc, schema) 验证JSON文档是否符合JSON Schema
 * @method static operate JSON_SCHEMA_VALIDATION_REPORT(string $json_doc, string $schema) - JSON_SCHEMA_VALIDATION_REPORT(json_doc, schema) 返回JSON验证报告
 * @method static operate JSON_SEARCH(string $json_doc, string $path) - JSON_SEARCH(json_doc, path) 返回JSON文档中值的路径
 * @method static operate JSON_SET(string $json_doc, string $path, mixed $value) - JSON_SET(json_doc, path, value) 在JSON文档中插入数据
 * @method static operate JSON_STORAGE_FREE(string $json_doc) - JSON_STORAGE_FREE(json_doc) 返回JSON列值部分更新后释放的空间
 * @method static operate JSON_STORAGE_SIZE(string $json_doc) - JSON_STORAGE_SIZE(json_doc) 返回JSON文档存储空间大小
 * @method static operate JSON_TABLE(string $json_expr, string $path) - JSON_TABLE(json_expr, path) 将JSON表达式作为关系表返回
 * @method static operate JSON_TYPE(string $json_doc) - JSON_TYPE(json_doc) 返回JSON值类型
 * @method static operate JSON_UNQUOTE(string $json_doc) - JSON_UNQUOTE(json_doc) 去引号JSON值
 * @method static operate JSON_VALID(string $json_doc) - JSON_VALID(json_doc) 判断JSON值是否有效
 * @method static operate JSON_VALUE(string $json_doc, string $path) - JSON_VALUE(json_doc, path) 从JSON文档提取值
 * @method static operate LAG(mixed $expr) - LAG(expr) 返回当前行前的值（窗口帧）
 * @method static operate LAST_DAY(string $date) - LAST_DAY(date) 返回指定月份的最后一天
 * @method static operate LAST_INSERT_ID() - LAST_INSERT_ID() 返回最后插入的自增列值
 * @method static operate LAST_VALUE(mixed $expr) - LAST_VALUE(expr) 返回窗口帧中最后一行的值
 * @method static operate LCASE(string $str) - LCASE(str) 返回小写字符串（LOWER的别名）
 * @method static operate LEAD(mixed $expr) - LEAD(expr) 返回当前行后的值（窗口帧）
 * @method static operate LEAST(mixed ...$expr) - LEAST(expr1, expr2, ...) 返回最小值
 * @method static operate LEFT(string $str, int $len) - LEFT(str, len) 返回字符串左部指定字符数
 * @method static operate LENGTH(string $str) - LENGTH(str) 返回字符串字节数
 * @method static operate LN(float $expr) - LN(expr) 返回自然对数
 * @method static operate LOAD_FILE(string $file_path) - LOAD_FILE(file_path) 加载指定文件
 * @method static operate LOCALTIME() - LOCALTIME() 返回当前本地时间（NOW()的别名）
 * @method static operate LOCALTIMESTAMP() - LOCALTIMESTAMP() 返回当前本地时间戳（NOW()的别名）
 * @method static operate LOCATE(string $substr, string $str) - LOCATE(substr, str) 返回子串首次出现位置
 * @method static operate LOG(float $expr) - LOG(expr) 返回自然对数
 * @method static operate LOG10(float $expr) - LOG10(expr) 返回以10为底的对数
 * @method static operate LOG2(float $expr) - LOG2(expr) 返回以2为底的对数
 * @method static operate LOWER(string $str) - LOWER(str) 返回小写字符串
 * @method static operate LPAD(string $str, int $len, string $pad_str) - LPAD(str, len, pad_str) 字符串左补指定字符
 * @method static operate LTRIM(string $str) - LTRIM(str) 删除开头空格
 * @method static operate MAKE_SET(int $bits, string ...$str) - MAKE_SET(bits, str1, str2, ...) 返回设置位的字符串集合
 * @method static operate MAKEDATE(int $year, int $day_of_year) - MAKEDATE(year, day_of_year) 由年和年中天数创建日期
 * @method static operate MAKETIME(int $hour, int $minute, int $second) - MAKETIME(hour, minute, second) 由小时、分钟、秒创建时间
 * @method static operate MASTER_POS_WAIT(string $pos) - MASTER_POS_WAIT(pos) 等待从属节点应用到指定位置
 * @method static operate MATCH(string $str) _MATCH(str) AGAINST (query) 全文搜索
 * @method static operate MAX(mixed $expr) - MAX(expr) 返回最大值
 * @method static operate MD5(string $str) - MD5(str) 计算MD5校验和
 * @method static operate MEMBER_OF(string $json_doc, string $json_array) - MEMBER OF(json_doc, json_array) 判断JSON数组是否包含指定元素
 * @method static operate MICROSECOND(string $time) - MICROSECOND(time) 返回微秒
 * @method static operate MID(string $str, int $start, int $len) - MID(str, start, len) 返回从指定位置开始的子串
 * @method static operate MIN(mixed $expr) - MIN(expr) 返回最小值
 * @method static operate MINUTE(string $time) - MINUTE(time) 返回分钟
 * @method static operate MOD(int $expr1, int $expr2) - MOD(expr1, expr2) 返回余数
 * @method static operate MONTH(string $date) - MONTH(date) 返回月份
 * @method static operate MONTHNAME(string $date) - MONTHNAME(date) 返回月份名称
 * @method static operate NAME_CONST(string $col_name, mixed $value) - NAME_CONST(col_name, value) 为列指定名称
 * @method static operate NOT_EXISTS(mixed $subquery) - NOT EXISTS(subquery) 判断子查询是否无行
 * @method static operate NOW() - NOW() 返回当前日期和时间
 * @method static operate NTH_VALUE(mixed $expr) - NTH_VALUE(expr) 返回窗口帧中第N行的值
 * @method static operate NTILE(int $n) - NTILE(n) 返回分区内的桶编号
 * @method static operate NULLIF(mixed $expr1, mixed $expr2) - NULLIF(expr1, expr2) 如果两个表达式相等则返回NULL
 * @method static operate OCT(int $num) - OCT(num) 返回数字的八进制字符串表示
 * @method static operate OCTET_LENGTH(string $str) - OCTET_LENGTH(str) 返回字符串字节数（LENGTH的别名）
 * @method static operate ORD(string $str) - ORD(str) 返回字符串最左字符的ASCII码
 * @method static operate PERCENT_RANK() - PERCENT_RANK() 返回百分位排名
 * @method static operate PERIOD_ADD(int $period, int $months) - PERIOD_ADD(period, months) 将月份加到年月上
 * @method static operate PERIOD_DIFF(int $period1, int $period2) - PERIOD_DIFF(period1, period2) 返回两个时期之间的月份数
 * @method static operate PI() - PI() 返回π值
 * @method static operate POSITION(string $substr, string $str) - POSITION(substr IN str) 返回子串首次出现位置（LOCATE的别名）
 * @method static operate POW(float $expr1, float $expr2) - POW(expr1, expr2) 返回指数值
 * @method static operate POWER(float $expr1, float $expr2) - POWER(expr1, expr2) 返回指数值
 * @method static operate RAND() - RAND() 返回随机浮点数
 * @method static operate RANDOM_BYTES(int $n) - RANDOM_BYTES(n) 返回随机字节数组
 * @method static operate RANK() - RANK() 返回分区内的排名（有间隙）
 * @method static operate REGEXP_INSTR(string $str, string $pattern) - REGEXP_INSTR(str, pattern) 返回匹配正则表达式的起始位置
 * @method static operate REGEXP_LIKE(string $str, string $pattern) - REGEXP_LIKE(str, pattern) 是否匹配正则表达式
 * @method static operate REGEXP_REPLACE(string $str, string $pattern, string $replacement) - REGEXP_REPLACE(str, pattern, replacement) 替换正则表达式匹配部分
 * @method static operate REGEXP_SUBSTR(string $str, string $pattern) - REGEXP_SUBSTR(str, pattern) 返回正则表达式匹配子串
 * @method static operate RELEASE_ALL_LOCKS() - RELEASE_ALL_LOCKS() 释放所有当前命名锁
 * @method static operate RELEASE_LOCK(string $lock_name) - RELEASE_LOCK(lock_name) 释放命名锁
 * @method static operate REPEAT(string $str, int $n) - REPEAT(str, n) 返回字符串重复n次
 * @method static operate REPLACE(string $str, string $old, string $new) - REPLACE(str, old, new) 替换字符串中指定部分
 * @method static operate REVERSE(string $str) - REVERSE(str) 返回字符串反转
 * @method static operate RIGHT(string $str, int $len) - RIGHT(str, len) 返回字符串右部指定字符数
 * @method static operate ROUND(float $expr, int $dec_places) - ROUND(expr, dec_places) 四舍五入
 * @method static operate ROW_COUNT() - ROW_COUNT() 返回受影响的行数
 * @method static operate ROW_NUMBER() - ROW_NUMBER() 返回当前行在分区中的序号
 * @method static operate RPAD(string $str, int $len, string $pad_str) - RPAD(str, len, pad_str) 字符串右补指定字符
 * @method static operate RTRIM(string $str) - RTRIM(str) 删除末尾空格
 * @method static operate SCHEMA() - SCHEMA() 返回当前数据库名（DATABASE的别名）
 * @method static operate SEC_TO_TIME(int $seconds) - SEC_TO_TIME(seconds) 将秒转换为时间格式
 * @method static operate SECOND(string $time) - SECOND(time) 返回秒数
 * @method static operate SESSION_USER() - SESSION_USER() 返回当前用户（USER的别名）
 * @method static operate SHA1(string $str) - SHA1(str) 计算SHA-1校验和
 * @method static operate SHA2(string $str, int $bits) - SHA2(str, bits) 计算SHA-2校验和
 * @method static operate SIGN(float $expr) - SIGN(expr) 返回表达式的符号
 * @method static operate SIN(float $expr) - SIN(expr) 返回正弦值
 * @method static operate SLEEP(int $seconds) - SLEEP(seconds) 睡眠指定秒数
 * @method static operate SOUNDEX(string $str) - SOUNDEX(str) 返回声音相似字符串
 * @method static operate SPACE(int $n) - SPACE(n) 返回n个空格字符串
 * @method static operate SQRT(float $expr) - SQRT(expr) 返回平方根
 * @method static operate STATEMENT_DIGEST() - STATEMENT_DIGEST() 计算语句摘要哈希值
 * @method static operate STATEMENT_DIGEST_TEXT() - STATEMENT_DIGEST_TEXT() 计算标准化语句摘要
 * @method static operate STD(mixed $expr) - STD(expr) 返回总体标准差
 * @method static operate STDDEV(mixed $expr) - STDDEV(expr) 返回总体标准差
 * @method static operate STDDEV_POP(mixed $expr) - STDDEV_POP(expr) 返回总体标准差
 * @method static operate STDDEV_SAMP(mixed $expr) - STDDEV_SAMP(expr) 返回样本标准差
 * @method static operate STR_TO_DATE(string $str, string $format) - STR_TO_DATE(str, format) 将字符串转换为日期
 * @method static operate STRCMP(string $str1, string $str2) - STRCMP(str1, str2) 比较两个字符串
 * @method static operate SUBDATE(string $date, string $interval) - SUBDATE(date, interval) 从日期减去时间间隔（DATE_SUB的别名）
 * @method static operate SUBSTR(string $str, int $start, int $len) - SUBSTR(str, start, len) 返回子串
 * @method static operate SUBSTRING(string $str, int $start, int $len) - SUBSTRING(str, start, len) 返回子串
 * @method static operate SUBSTRING_INDEX(string $str, string $delimiter, int $count) - SUBSTRING_INDEX(str, delimiter, count) 返回子字符串（根据分隔符次数）
 * @method static operate SUBTIME(string $time1, string $time2) - SUBTIME(time1, time2) 时间相减
 * @method static operate SUM(mixed $expr) - SUM(expr) 返回总和
 * @method static operate SYSDATE() - SYSDATE() 返回函数执行时的时间
 * @method static operate SYSTEM_USER() - SYSTEM_USER() 返回当前用户（USER的别名）
 * @method static operate TAN(float $expr) - TAN(expr) 返回正切值
 * @method static operate TIME(string $datetime) - TIME(datetime) 提取时间部分
 * @method static operate TIME_FORMAT(string $time, string $format) - TIME_FORMAT(time, format) 格式化时间
 * @method static operate TIME_TO_SEC(string $time) - TIME_TO_SEC(time) 将时间转换为秒
 * @method static operate TIMEDIFF(string $time1, string $time2) - TIMEDIFF(time1, time2) 计算时间差
 * @method static operate TIMESTAMP(mixed $expr) - TIMESTAMP(expr) 返回日期或时间表达式；两个参数则求和
 * @method static operate TIMESTAMPADD(string $unit, int $interval, string $datetime) - TIMESTAMPADD(unit, interval, datetime) 将时间间隔加到日期表达式上
 * @method static operate TIMESTAMPDIFF(string $unit, string $datetime1, string $datetime2) - TIMESTAMPDIFF(unit, datetime1, datetime2) 计算两个日期表达式之间的差值
 * @method static operate TO_BASE64(string $str) - TO_BASE64(str) 将字符串转换为Base64字符串
 * @method static operate TO_DAYS(string $date) - TO_DAYS(date) 将日期转换为天数
 * @method static operate TO_SECONDS(string $date) - TO_SECONDS(date) 将日期或时间转换为自公元0年的秒数
 * @method static operate TRIM(string $str) - TRIM(str) 删除首尾空格
 * @method static operate TRUNCATE(float $expr, int $dec_places) - TRUNCATE(expr, dec_places) 截断到指定小数位数
 * @method static operate UCASE(string $str) - UCASE(str) 返回大写字符串（UPPER的别名）
 * @method static operate UNCOMPRESS(string $str) - UNCOMPRESS(str) 解压缩字符串
 * @method static operate UNCOMPRESSED_LENGTH(string $str) - UNCOMPRESSED_LENGTH(str) 返回压缩前字符串长度
 * @method static operate UNHEX(string $hex_str) - UNHEX(hex_str) 返回十六进制字符串对应的值
 * @method static operate UNIX_TIMESTAMP() - UNIX_TIMESTAMP() 返回Unix时间戳
 * @method static operate UPPER(string $str) - UPPER(str) 返回大写字符串
 * @method static operate USER() - USER() 返回客户端提供的用户名和主机名
 * @method static operate UTC_DATE() - UTC_DATE() 返回当前UTC日期
 * @method static operate UTC_TIME() - UTC_TIME() 返回当前UTC时间
 * @method static operate UTC_TIMESTAMP() - UTC_TIMESTAMP() 返回当前UTC日期和时间
 * @method static operate UUID() - UUID() 返回UUID
 * @method static operate UUID_SHORT() - UUID_SHORT() 返回整数UUID
 * @method static operate UUID_TO_BIN(string $uuid_str) - UUID_TO_BIN(uuid_str) 将字符串UUID转换为二进制
 * @method static operate VALIDATE_PASSWORD_STRENGTH(string $password) - VALIDATE_PASSWORD_STRENGTH(password) 判断密码强度
 * @method static operate VALUES(mixed ...$expr) - VALUES(expr1, expr2, ...) 定义INSERT语句中的值
 * @method static operate VAR_POP(mixed $expr) - VAR_POP(expr) 返回总体方差
 * @method static operate VAR_SAMP(mixed $expr) - VAR_SAMP(expr) 返回样本方差
 * @method static operate VARIANCE(mixed $expr) - VARIANCE(expr) 返回总体方差
 * @method static operate VERSION() - VERSION() 返回MySQL服务器版本
 * @method static operate WAIT_FOR_EXECUTED_GTID_SET(string $gtid_set) - WAIT_FOR_EXECUTED_GTID_SET(gtid_set) 等待从属节点执行指定GTID
 * @method static operate WEEK(string $date) - WEEK(date) 返回周数
 * @method static operate WEEKDAY(string $date) - WEEKDAY(date) 返回星期索引
 * @method static operate WEEKOFYEAR(string $date) - WEEKOFYEAR(date) 返回年中的周数（1-53）
 * @method static operate WEIGHT_STRING(string $str) - WEIGHT_STRING(str) 返回字符串权重
 * @method static operate YEAR(string $date) - YEAR(date) 返回年份
 * @method static operate YEARWEEK(string $date) - YEARWEEK(date) 返回年和周数
 *
 */
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
	public array $params = [];//执行参数
	//链式收集信息
	protected(set) mixed $select = null;
	protected array $where = [], $join = [], $having = [], $set = [], $options = [];
	protected ?array $limit = null, $sort = null, $group = null;
	protected string $action = 'select';
	public static ?sql $current = null;
	protected(set) ?string $alias=null;
	protected \WeakMap|null $joinSQL =null;
	public function __construct(protected(set) table $table){}
	public function as(string $alias): static{//用于join (select ...) as
		$clone = clone $this;
		$clone->alias = $alias;
		return $clone;
	}
	public function collectParam(mixed $value): ?string{
		if(!self::$current) return null;
		$this->params[] = $value;
		return '?';
	}
	public function hasJoin(): bool{
		return !empty($this->join);
	}
	public static function table(string $name, string $primary = 'id', ?pdo $db = null): table{
		return new table($name, $primary, $db);
	}
	public static function value($any): value{
		return new value($any);
	}
	public function execute(?pdo $db = null): result{
		return ($pdo = $db ?? $this->table->db) ? match ($this->action) {
			'insert' => $pdo->insert((string)$this, $this->params),
			'update', 'delete' => $pdo->execute((string)$this, $this->params),
			'select' => $pdo->select((string)$this, $this->params),
			default => new result(false),
		} : new result(false);
	}
	public function insert(array $fields = [], array $options = []): static{
		$this->action = 'insert';
		$this->set = $fields;
		$this->options = $options;
		return $this;
	}
	public function update(array $fields = [], array $options = []): static{
		$this->action = 'update';
		$this->set = $fields;
		$this->options = $options;
		return $this;
	}
	public function delete(array $options = []): static{
		$this->action = 'delete';
		$this->options = $options;
		return $this;
	}
	public function select(array|string|expr|null $fields = [], array $options = []): static{
		$this->action = 'select';
		$this->select = $fields;
		$this->options = $options;
		return $this;
	}
	public function join(table|sql $table, mixed $on = null, array $options = []): static{
		if($table instanceof sql){
			$this->joinSQL ??=new \WeakMap();
			$this->joinSQL[$table->table] = $table;
			$table = $table->table;
		}
		$this->join[] = [$table, $on ?: [$table->primary => $this->table->primary], $options ?: ['LEFT']];
		return $this;
	}
	public function where(mixed ...$conditions): static{
		$this->where = $conditions;
		return $this;
	}
	public function limit(int $rows, int $offset = 0): static{
		$this->limit = [$rows, $offset];
		return $this;
	}
	public function page(int $page, int $max = 20): static{
		$this->limit = [$max, ($page - 1) * $max];
		return $this;
	}
	public function sort(array|string|expr|null $fields = null, string $direction = 'ASC'): static{
		$this->sort = [$fields, $direction];
		return $this;
	}
	public function group(array|string|expr|null $fields = [], string $sort = 'ASC'): static{
		$this->group = [$fields, $sort];
		return $this;
	}
	public function having(mixed ...$conditions): static{
		$this->having = $conditions;
		return $this;
	}
	public function __toString(): string{
		self::$current ??= $this;
		$this->params = [];
		$sql = match ($this->action) {
			'select' => $this->buildSelect(),
			'update' => $this->buildUpdate(),
			'delete' => $this->buildDelete(),
			'insert' => $this->buildInsert(),
			default => 'DO 1',
		};
		self::$current = null;
		return $this->alias ? "( $sql ) as $this->alias" : $sql;
	}
	private function buildDelete(): string{
		$priority = $this->options['priority'] ?? false ? ' LOW_PRIORITY' : '';
		$ignore = $this->options['ignore'] ?? false ? ' IGNORE' : '';
		$quick = $this->options['quick'] ?? false ? ' QUICK' : '';
		$where = $this->buildWhere($this->where);
		$sort = $this->buildSort($this->sort);
		$limit = $this->buildLimit($this->limit);
		return "DELETE$priority$quick$ignore FROM $this->table$where$sort$limit";
	}
	private function buildInsert(): string{
		$priority = $this->options['priority'] ?? false ? ' ' . strtoupper($this->options['priority']) : '';
		$ignore = $this->options['ignore'] ?? false ? ' IGNORE' : '';
		[$cols, $prepares, $params] = $this->buildInsertValue($this->set);
		$this->params = $params;
		return "INSERT$priority$ignore INTO $this->table ($cols) VALUES ($prepares)";
	}
	private function buildInsertValue($set): array{
		if(!is_array($set) || empty($set)) return [[], [], null];
		$cols = current($set);
		if(!is_array($cols)){
			$cols = $set;
			$_set = [$set];
		}
		else $_set = $set;
		$_cols = [];
		$_prepares = [];
		foreach($cols as $col => $value){
			$_cols[] = "`$col`";
			$_prepares[] = '?';
		}
		$params = [];
		foreach($_set as $index => $values){
			$params[$index] = array_values($values);
		}
		return [implode(', ', $_cols), implode(', ', $_prepares), $params];
	}
	private function buildUpdate(): string{
		$priority = ($this->options['priority'] ?? false) ? ' ' . strtoupper($this->options['priority']) : '';
		$ignore = ($this->options['ignore'] ?? false) ? ' IGNORE' : '';
		$set = $this->buildSet($this->set);
		$where = $this->buildWhere($this->where);
		$sort = $this->buildSort($this->sort);
		$limit = $this->buildLimit($this->limit);
		return "UPDATE$priority$ignore $this->table$set$where$sort$limit";
	}
	private function buildSelect(): string{
		$select = $this->buildFields($this->table, $this->select, !$this->join);
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
		return "SELECT$options $select FROM $this->table$join$where$group$having$sort$limit";
	}
	private function buildJoin(array $joins): array{
		$joinStr = $select = '';
		foreach($joins as [$table2, $on, $options]){
			$opts = $this->buildOptions(['NATURAL', 'INNER', 'CROSS', 'LEFT', 'RIGHT'], $options);
			$keyword = in_array('STRAIGHT', $options) ? 'STRAIGHT_JOIN' : 'JOIN';
			if(!is_array($on)) $on = [$on => $on];
			$conditions = [];
			foreach($on as $k => $v){
				$leftField =match (true){
					$k instanceof expr => $k,
					is_string($k) =>$table2[$k],
					default=>$table2[null],// ['id', ...]
				};
				$rightField =match (true) {
					$v instanceof expr => $v,
					is_string($v) =>$this->table[$v],
					default =>new value($v),
				};
				$conditions[] = "$leftField = $rightField";
			}
			if($this->joinSQL && isset($this->joinSQL[$table2])){
				$_sql =$this->joinSQL[$table2];
				if($_sql?->alias) $table2 = $_sql;// join (select ...) as
				else {
					$_select =$this->buildFields($table2, $_sql->select, !$this->join);
					$select .= ($select ? ', ' : '') .$_select;
				}
			}
			$joinStr .= "$opts $keyword $table2 ON (" . implode(' AND ', $conditions) . ')';
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
					if($value instanceof expr) $_conditions[] = $value;
					elseif(is_array($value)){
						$fn = $value['fn'] ?? null;
						unset($value['fn']);
						$_conditions[] = $fn ? $this->table[$field]->$fn(...$value) : $this->table[$field]->in($value);
					}
					else $_conditions[] = $this->table[$field]->eq($value);
				}
			}
			else $_conditions[] = $cond instanceof expr ? $cond : $this->table[$this->table->primary]->eq($cond);
		}
		return !$_conditions ? "" : " $command " . implode(' AND ', $_conditions);
	}
	protected function buildFields(table $table, $select=null, bool $only = true): string{
		if(null === $select) return '';
		$_select = is_array($select) ? $select : [$select];
			if(empty($_select)) return ($only ? '*' : (string)$table['*']);
			$vs =[];
			foreach($_select as $value){
					if($value instanceof expr) $vs[] = $value;
					elseif(is_string($value)) $vs[] =$table[$value];
					else $vs[] =new value($value);
			}
			return implode(', ', $vs);
	}
	protected function buildSet(array $set): string{
		if(!$set) return '';
		$params = [];
		foreach($set as $field => $value){
			$params[] = $this->table[$field]->eq($value);
		}
		return ' SET ' . implode(', ', $params);
	}
	protected function buildSort(?array $sort, string $command = "ORDER"): string{
		if(empty($sort)) return '';
		[$field, $asc] = $sort;
		$sorts = [];
		if(is_array($field)){
			foreach($field as $f => $s){
				$fieldName = is_string($f) ? $this->table[$f] : (is_numeric($f) ? $this->table[$s] : (string)$f);
				$direction = is_bool($s) || is_string($s) ? (strtoupper($s[0] ?? 'A') === 'A' ? 'ASC' : 'DESC') : ($asc ? 'ASC' : 'DESC');
				$sorts[] = 'GROUP' === $command ? $fieldName : "$fieldName $direction";
			}
		}
		elseif($field instanceof expr || is_string($field)){
			$fieldName = $field instanceof expr ? ($field->alias ? "`{$field->alias}`" : (string)$field) : (string)$this->table[$field];
			$direction = is_bool($asc) || is_string($asc) ? (strtoupper($asc[0] ?? 'A') === 'A' ? 'ASC' : 'DESC') : 'ASC';
			$sorts[] = 'GROUP' === $command ? $fieldName : "$fieldName $direction";
		}
		return empty($sorts) ? '' : " $command BY " . implode(", ", $sorts);
	}
	protected function buildLimit(?array $limit): string{
		return $limit ? ($limit[1] ? " LIMIT $limit[1], $limit[0]" : " LIMIT $limit[0]") : '';
	}
	public static function __callStatic($name, $arguments): operate{
		return new operate($name, $arguments);
	}
	public function offsetSet($offset, $value): void{}
	public function offsetExists($offset): bool{ return false; }
	public function offsetUnset($offset): void{}
	public function offsetGet(mixed $offset): ?table{
		if(!is_string($offset)) return null;
		if($offset === $this->table->name || $offset === $this->table->alias) return $this->table;
		foreach($this->join as [$table, , ]){
			if($offset === $table->name || $offset === $table->alias) return $table;
		}
		return null;
	}
	public function __debugInfo(): array{
		return [
			'table' => "$this->table {$this->table->alias}",
			'action'=>$this->action,
			'params'=>$this->params,
			'select'=>$this->select,
			'where'=>$this->where,
			'join'=>$this->join,
			'limit'=>$this->limit,
			'sort'=>$this->sort,
			'group'=>$this->group,
		];
	}}