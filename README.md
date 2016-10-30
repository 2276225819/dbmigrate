# Migrations


## 实例化类
    DBMigrate::construct($pdo[,$config]):$this  
    // @pdo 数据库连接对象
    // @config 配置
    // @config['prefix']:string // 数据库前缀
    // @config['keys']:array    // 数据库类型关键字补充 
    // @config['exts']:array    // 数据库关键字补充 
    // @config['column']:array  // 数据库表定义补充


# 数据库定义
### 表定义
    DBMigrate::table($name[,$def]) // 链式操作,
    // @name:string //数据表名
    // @def:string //可选，数据库字段定义补充
### 已定义的字段类型(如果不存在需要的类型要在初始化函数补充)
    DBMigrate::int() // 链式操作,
    DBMigrate::integer()
    DBMigrate::tinyint()
    DBMigrate::bigint()
    DBMigrate::decimal(m,d)
    DBMigrate::double()
    DBMigrate::date()
    DBMigrate::time()
    DBMigrate::datetime()
    DBMigrate::varchar(m)
    DBMigrate::string(m)
    DBMigrate::text()
    DBMigrate::char(m) 
### 已定义的字段关键字(如果不存在需要的关键字要在初始化函数补充)
    DBMigrate::comment(m) // 链式操作,
    DBMigrate::default(m)


# 数据操作
### 执行的操作不会提交到数据库 
    DBMigrate::check():$this    
    // @return:$this //链式操作
 
### 数据库同步，数据库存在但字段不同则修改，数据库不存在但已字段定义则添加
    DBMigrate::sync([$before])
    // @before:closure //可选，前置操作
    // @return:$this //链式操作


### 数据库清理，删除在数据库存在且未定义的字段 
    DBMigrate::clean([$before]) 
    // @before:closure //可选，前置操作
    // @return:$this //链式操作


### 辅助函数,清空表数据 
    DBMigrate::truncate([$before])  
    // @before:closure //可选，前置操作
    // @return:$this //链式操作


### 辅助函数,输出数据库表定义数组
    DBMigrate::export() 
    // @return:array  

# Example
    $pdo = new PDO("mysql:dbname=software");
    $dbm = new DBMigrate($pdo);
    $dbm->table('user') //数据表定义
        ->increment('id')
        ->varchar('username',32)  ->comment('账户')
        ->varchar('password',32) //->comment('密码')//不需要可用注释掉
    ;//链式操作结束
    $dbm->sync();//同步数据库
    print_r($dbm->log);//输出数据库操作记录
    
## TODO
回迁记录
回迁功能（撤销