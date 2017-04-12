<?php namespace DBMigrate;
use \PDO;
use \Exception;

class DB{ 
    public $pdo;
    public $sql="";
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
    public function all(string $sql,$args=array()){
        return $this->run($sql,$args)->fetchAll(); 
    }
    public function row(string $sql,$args=array()){
        return $this->run($sql,$args)->fetch();  
    } 
    public function cell(string $sql,$args){
        return $this->run($sql,$args)->fetch()[0];   
    } 
    public function run(string $sql,$args=array(),$return=false){ 
        $pdo = $this->pdo;
        $this->q($sql,$args);  
        $this->args = array_merge($this->args,$args);
        $this->log[]=$this->sql;
        if($return) return $this->sql;
        $query = $pdo->prepare($this->sql); 
        $res = $query->execute($this->args);   
        $this->args=[];
        $this->sql="";
        if(empty($res)) {
            list($c,$n,$d)=$query->errorInfo();
            throw new Exception("Error:".$sql, 1); 
        }  
        return $query;  

    }
    public function q(string $sql,$args=array()):DB{ 
        $pf  = $this->prefix;  
        $this->sql.= preg_replace(
                array('/((?:join|truncate|into|from|create table|alter table|as)\s+)([\w]+)/' ,
                        '/(\w+)\s+(read|write|set)/',  '(\w+\.[\w\*]+)'),
                array("$1 `$pf$2`","`$pf$1` $2" ,"`$pf$0`"), $sql?$sql:'');    
        $this->args = array_merge($this->args,$args);
        return $this;
    } 
    public function qSet(array $arr):DB{  
        $sql = ""; $this->args=[];
        foreach ($arr as $key => $value){
            $sql.=",`{$key}`=?";
            $this->args[]=$value;
        }
        $this->sql.= " SET ".substr($sql,1);
        return $this;
    }
    public function qValues(...$args):DB{ 
        $sql1 = ""; $sql2=""; $this->args=[];  
        foreach ($args[0] as $key => $value)
            $sql1.=",`{$key}`"; 
        foreach ($args as $arr){  
            $sql2.=",(".substr(str_repeat(",?",count($arr)),1).")";
            array_push($this->args,...array_values($arr)); 
        }  
        $this->sql.=" (".substr($sql1,1)." )VALUES".substr($sql2,1)."";  
        return $this;
    } 
 
}

//$d = new DB(new PDO("mysql:dbname=test","root","root"));    
//  foreach( $d->run("select * from u_user") as $r)
//     print_r($r);