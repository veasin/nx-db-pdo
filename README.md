# nx-db-pdo

pdo for nx


> composer require veasin/nx-db-pdo

```php
'db/pdo'=>[
    'default' => [
        'dsn' => 'mysql:dbname=db_name;host=host_url;charset=utf8mb4',
        'username' => 'db_user',
        'password' => 'db_password',
        'options' => [
        ],
    ],
]
```
```php
class app extends nx\app{
    use \nx\parts\db\pdo;
} 
```


```php
$this->db()->execute("SELECT * FROM `user`");
$this->table("user")->select()->execute();
//$this->db()->from("user");
```