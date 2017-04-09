<?php namespace Mimix;

class DBSync{ 

    public $db;
    public $file; 
	public $debug;
    public $tables;
    public function __construct($file=__DIR__.'/../temp.sql',$debug=false){  
        $this->file=$file;    
		$this->debug=$debug;  
		
		$txt = file_get_contents($this->file);  
        $offset = 0; 
        while($table = TableBlock::read($txt,$offset)) 
            $this->tables[$table->name] = $table;  

    }   

    /** 
     * @param PDO $pdo 
     * @return void
     */
    public function setPDO(PDO $pdo){
        $this->db = new DB($pdo);  
    }
	/**
	 * 加载数据库结构
	 *
	 * @return void
	 */
	public function loadrt(){
		foreach ($this->db->q("show tables") as $key => $value) { 
            $row = $this->db->row("show create table {$value[0]}");  
            $t = TableBlock::read($row[1]);  
			$tables[$t->name]=$t;
		}
		return $tables;
	}
 
	/**
	 * 数据库结构读取到本地
	 * @param boolean $focus //不合并结构重新写入
	 * @return void
	 */
    public function merge($focus=false){ 
  		foreach ($this->loadrt() as $name => $table) {  			
 			if(empty($this->tables[$table->name]) || $focus){ 
                $this->tables[$table->name] = $table;    
            } else{ 
                $this->tables[$table->name]->mergeFrom($table);  
            }
        }  
    } 
	/**
	 * 生成sql添加修改语句
     * @param boolean $focus //生成删除语句
	 * @return string[] 
	 */
	public function diff($focus=false){   
		$tables = $this->loadrt(); 
		foreach ($this->tables as $tn => $table) { 
			$qs = array_merge($qs??[],$table->diffFrom($tables[$tn]??null)); 
		}  
		if($focus)foreach($tables as $tn=>$table){
			$qs = array_merge($qs??[],$table->clearFrom($this->tables[$tn]??null));  
		}
		return $qs??[];
	}
 


    /** 
     * 数据库写入到文件
     * @param boolean $focus //不合并本地表结构重新写入
     * @return void
     */
    public function pull($focus=false){  
        $this->merge($focus);   
        foreach ($this->tables as $value) 
            $str=($str??'')."{$value};\n\n"; 
       
        file_put_contents($this->file,$str);   
    }    
    /**  
     * 数据库应用本地表结构 
     * @param boolean $focus //删除本地不存在的数据库表
     * @return void
     */
    public function push($focus=false){  
		$qs = $this->diff($focus);
		foreach ($qs as $value) 
			$this->db->q($value);  
		return count($qs);
    } 
}

// $db = new DBSync(__DIR__."/dd.sql");  
// $db->setPDO(new PDO('mysql:host=localhost;dbname=test','root','root'));
// $db->pull();
// print_r($db->diff(true));
// $db->push(true);
 