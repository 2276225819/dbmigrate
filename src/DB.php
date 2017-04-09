<?php namespace DBMigrate;
use \PDO;
use \Exception;

class DB{ 
    public $pdo;
    public $log=[];
	public $args=[];
    public function __construct(PDO $pdo,$pf=''){
        $this->pdo = $pdo;
        $this->prefix = $pf; 
    }
    public function __call($name,$args){
        $head = str_replace('_',' ',$name); 
        return $this->q($head.' '.$args[0],$args[1]??array()); 
    }
    public function run($sql,$args=array()){
        return $this->q($sql,$args)->fetchAll(); 
    }
    public function row($sql,$args=array()){
        return $this->q($sql,$args)->fetch();  
    } 
    public function cell($sql,$args){
        return $this->q($sql,$args)->fetch()[0];   
    } 
    public function bulidSet($arr){  
        $sql = ""; $this->args=[];
        foreach ($arr as $key => $value){
            $sql.=",`{$key}`=?";
            $this->args[]=$value;
        }
        return " SET ".substr($sql,1);
    }
    public function bulidValues(...$args){ 
        $sql1 = ""; $sql2=""; $this->args=[];  
        foreach ($args[0] as $key => $value)
            $sql1.=",`{$key}`"; 
        foreach ($args as $arr){  
            $sql2.=",(".substr(str_repeat(",?",count($arr)),1).")";
            array_push($this->args,...array_values($arr)); 
        }  
        return " (".substr($sql1,1)." )VALUES".substr($sql2,1)."";  
    } 
    public function q($sql,$args=array(),$check=false) : PDOStatement{ 
        $pdo = $this->pdo;
        $pf  = $this->prefix;   
        $sql = preg_replace(
                array('/((?:join|truncate|into|from|create table|alter table|as)\s+)([\w]+)/' ,
                        '/(\w+)\s+(read|write|set)/',  '(\w+\.[\w\*]+)'),
                array("$1 `$pf$2`","`$pf$1` $2" ,"`$pf$0`"), $sql );    
        $this->args = array_merge($this->args,$args);
        $this->log[]=$sql;
        if($check) return true; 
        $query = $pdo->prepare($sql); 
        $res = $query->execute($this->args);   
        $this->args=[];
        if(empty($res)) {
            list($c,$n,$d)=$query->errorInfo();
            throw new Exception("Error:".$d, 1); 
        }  
        return $query;  
    } 
 
}

// $d = new DB(new PDO("mysql:dbname=test","root","root"));    
// print_r($d->q("insert d_model".$d->bulidValues(array(
//     'ID'=>2
// ))));
 