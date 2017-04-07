<?php
include "DB.php";
include "TableBlock.php";

class DBSync{ 

    public $db;
    public $file;
    public $tables;
    public $remote;
    public function __construct($file=__DIR__.'/temp.sql'){  
        $this->file=$file;  
        
        $txt = file_get_contents($file);  
        $offset = 0; 
        while($table = TableBlock::read($txt,$offset)) 
            $this->tables[$table->name] = $table;  
    }      
    /** 
     * @param PDO $pdo
     * @param boolean $focus
     * @return void
     */
    public function setPDO(PDO $pdo){
        $this->db = new DB($pdo);  
    }
 
    public function merge($focus=false){
        foreach ($this->remote as $table) {  
            if(isset($this->tables[$table->name]) && !$focus){ 
                $this->tables[$table->name]->memgeTable($table);    
            } else{
                $this->tables[$table->name] = $table;  
            }
        }   
    } 
    /**
     * 查询数据库表
     *
     * @return void
     */
    public function fetch(){
        foreach ($this->db->q("show tables") as $key => $value) { 
            $row = $this->db->row("show create table {$value[0]}");  
            $table = TableBlock::read($row[1]);  
            $tables[$table->name]=$table; 
        } 
        $this->remote=$tables; 
    }


    /**
     * 加载数据库表到内存表
     * if true :不合并文件表到内存
     * 写入文件表
     *
     * @param string $file
     * @param boolean $focus
     * @return void
     */
    public function pull($focus=false){ 
        $this->fetch(); 
        $this->merge($focus);  

        foreach ($this->tables as $value) 
            $str=($str??'')."{$value};\n\n"; 
         
        file_put_contents($this->file,$str);  
        echo $str;
    }    
    /** 
     * 从内存表生成更新语句
     * if true 生成删除语句 
     * 应用数据库语句
     *
     * @param PDO $pdo
     * @param boolean $focus
     * @return void
     */
    public function push($focus=false){ 
        // $tables = $this->fetch();
        // foreach ($this->tables as $table) {
        //     if($tables[$table->name]){
        //         $sql = $tables[$table->name]->diffTable($table);
        //     }else{
        //         $sql = "$table;\n\n";
        //     }
        //     $this->db->q($sql); 
        // }
        
    }


 
}

$db = new DBSync(__DIR__."/dd.sql");  
$db->setPDO(new PDO('mysql:host=localhost;dbname=test','root','root'));
$db->pull(); 
// $db->push();
//print_r($db);


// $db->setPDO('db2',new PDO('mysql:host=localhost;dbname=test','root','root'));
// $db->setFile('db1',"temp.sql");

// $db->loadPDO(new PDO('mysql:host=localhost;dbname=bplus','root','root'));  
// $db->syncFile("dd.sql",true);
// $db->syncPDO(new PDO('mysql:host=localhost;dbname=test','root','root'));
// print_r($db);
 