<?php namespace Mimix;

class DB{ 
    public $pdo;
    public $log=array();
    public function __construct(\PDO $pdo,$pf=''){
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
                
        $this->log[]=$sql;
        if($check) return true; 
        $query = $pdo->prepare($sql); 
        $res = $query->execute($this->args??$args);   
        unset($this->args);
        if(empty($res)) {
            list($c,$n,$d)=$query->errorInfo();
            throw new Exception("Error:".$d, 1); 
        }  
        return $query;  
    } 
    public function _createTable($table,$column ){   
        $str = "create table {$table}(\n   ";
        $ext="";
        $_tag1=$_tag2='';
        foreach ($column as $key =>$value){ 
            if(!is_numeric($key)){
                $ext.="$_tag1 $key=$value "; 
                if(empty($_tag1)) $_tag1=",  "; 
            }else{
                $str.= $_tag2.$value;//arr[1];
                if(empty($_tag2)) $_tag2=",\n   ";   
            }
        }
        $str.="\n) $ext ;";    
        return $this->q($str,array(),$this->ischeck);  
    }
    public function _alterTable($table,$column ){   
        $result = $this->_query("show columns from $table");
        $format = $this->_query("show create table $table");
        $format = $format[0]["Create Table"];//
        $tps = array_column($result ,1); 
        $fns = array_column($result ,0);
        $fns = array_flip($fns);

        foreach ($column as $key => $value){ 
            if(!is_numeric($key)){  
                if(stristr($format,"$key=$value")===false){
                    $str = "alter table {$table} $key $value;";   
                    $this->q($str,array(),$this->ischeck);   
                } 
                continue;
            }
            list($name,$type) = explode(' ',$value);   
            if( isset($fns[$name]) ){  
                if( !strstr($tps[$fns[$name]],$type) ){ 
                    $str = "alter table {$table} change $name $value; -- old: {$tps[$fns[$name]]}";  
                    $this->q($str,array(),$this->ischeck);  
                }
            }else{
                $str = "alter table {$table} add  $value; ";  
                $this->q($str,array(),$this->ischeck);  
            } 
        }  
    }    
}

// $d = new DB(new PDO("mysql:dbname=test","root","root"));    
// print_r($d->insert_delayed("d_model".$d->bulidValues(array(
//     'ID'=>2
// ))));
 