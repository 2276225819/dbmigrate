# dbsync

下面是通用测试用例 
```sql
--temp.sql
create table user(
   id int ,
   name varchar(255)
);
 
--mysql -Dtest -uroot -proot
create table user(
   id int auto_increment,
   update_time time,
   primary key(id)
);
``` 




## 1.合并到数据库
```php
//demo.php
include __DIR__."/vendor/autoload.php";
$sync = new \xlx\DBSync('temp.sql'); 
$sync->setPDO(new PDO('mysql:dbname=test','root','root'));
$sync->push();
```
执行结果
```sql
--temp.sql
create table user(
   id int ,
   name varchar(255)
);
 
--mysql -Dtest -uroot -proot
create table user(
   id int ,
   update_time time,
   name varchar(255),
   primary key(id)
);
``` 



## 2.覆盖到数据库
```php
//demo.php
include __DIR__."/vendor/autoload.php";
$sync = new \xlx\DBSync('temp.sql'); 
$sync->setPDO(new PDO('mysql:dbname=test','root','root'));
$sync->push(true);
```
执行结果
```sql
--temp.sql
create table user(
   name varchar(255)
);
 
--mysql -Dtest -uroot -proot
create table user( 
   name varchar(255) 
);
``` 



## 3.合并到本地数据库文件
```php
//demo.php
include __DIR__."/vendor/autoload.php";
$sync = new \xlx\DBSync('temp.sql'); 
$sync->setPDO(new PDO('mysql:dbname=test','root','root'));
$sync->pull();
```
执行结果
```sql
--temp.sql
create table user(
   name varchar(255),
   id int auto_increment,
   update_time time,
   primary key(id)
);

--mysql -Dtest -uroot -proot
create table user(
   id int auto_increment,
   update_time time,
   primary key(id)
);
``` 


## 4.生成迁移SQL语句
```php
//demo.php
include __DIR__."/vendor/autoload.php";
$sync = new \xlx\DBSync('temp.sql'); 
$sync->setPDO(new PDO('mysql:dbname=test','root','root'));
print_r($sync->diff());
/* output:
array(
   0 => ALTER TABLE user CHANGE id id int;
   1 => ALTER TABLE user ADD name varchar(255);
)
*/
```