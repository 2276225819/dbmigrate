<?php 
if(!function_exists('array_column')){ 
    function array_column($arr,$col){  
        return array_map(function($v)use($col){return $v[$col];},$arr);
    } 
} 
class DBMigrate {
    public $pdo;
    public $exts;
    public $keys;
    public $tables; 
    public $prefix;
    public $engine;
    public $comment='\'DBMigrate\'';
    public $ischeck=false;
    public function __construct($pdo,$config=array()){ 
        if( empty($pdo) || !($pdo instanceof \PDO) )
            throw new Exception("DBMigrate Error: 'PDO Request'", 1);
        $this->pdo = $pdo;
        $this->prefix = isset($config['prefix'])?$config['prefix']:'';   
        $this->keys  = array(
            'int'=> 'int',
            'integer'=> 'int',
            'tinyint'=> 'tinyint',
            'bigint'=> 'bigint',
            
            'decimal'=> 'decimal',
            'double'=> 'double', 

            'date'=> 'date',
            'time'=> 'time',
            'datetime'=> 'datetime',

            'varchar'=> 'varchar',
            'string'=> 'varchar',
            'text'=> 'text',
            'char'=> 'char',
        ); 
        $this->exts = array(
            'comment'=> 'comment',
            'default'=> 'default',
        );
        $this->tables = array(

        );  
        if(isset($config['column']))
            $this->tables = array_merge($this->tables, $config['column']);  
        if(isset($config['keys']))
            $this->keys = array_merge($this->keys, $config['keys']); 
        if(isset($config['exts']))
            $this->exts = array_merge($this->exts,$config['exts']);  
        if(isset($config['engine']))
            $this->engine($this->engine = $config['engine']);
    }


    public $last_table='_'; 
    public $last_index=0;
    public function __call($name,$args){   
        //laravel  
        if($name=='table'){
            $this->last_table=$args[0]; 
            $this->last_index=0;
            $this->tables[$args[0]]['engine']=$this->engine;
            $this->tables[$args[0]]['comment']=$this->comment;
            if(isset($args[1])){  
                if(is_callable($args[1])) $args[1]($this);
                else{
                    if(empty($this->tables[$args[0]]))
                        $this->tables[$args[0]]=array();
                    $this->tables[$args[0]] = array_merge(
                        $this->tables[$args[0]], 
                        array_filter(preg_split("/[,\r\n\t][ \r\n\t]+/",$args[1]))
                    );  
                } 
            }
            return $this;
        }
        //if(empty($ntable=$this->last_table)){...}
        if($ntable = $this->last_table );else{
            return $this; 
        } 
        if($name == 'increment' || $name =='increments'){   
            $str = array_shift($args)." int auto_increment primary key";
            $this->tables[$ntable][]=$str; 
            $this->last_index++;
            return $this;
        }      
        if( isset( $this->keys[$name] ) ){ 
            $str = array_shift($args).' '.$this->keys[$name];
            if($args) $str.='('.join($args,',').')';
            $this->tables[$ntable][]=$str; 
            $this->last_index++;
            return $this;
        }   
        if( isset( $this->exts[$name] )){ 
            if($this->last_index==0){ 
                $this->tables[$ntable][$this->exts[$name]]=var_export($args[0],true);
            }else{
                $this->tables[$ntable][$this->last_index-1] .= " {$this->exts[$name]} ".var_export($args[0],true);  
            }
            return $this;
        }
    }
    public function engine($str){   
        static $engines;
        if(empty($engines)){ 
            $engines = array_column($this->_query("show engines"),0); 
        }   
        foreach ($engines as $type) { 
            if( stristr($type,$str) && strlen($type) === strlen($str) ){  
                if($this->last_table!='_'){ 
                    $this->tables[$this->last_table]['engine'] = $type; 
                }
                return $this;
            }
        }  
        throw new Exception("DBMigrate Error: 'Not support engine type ({$str})'", 1); 
    }
    public function check(){
        $this->ischeck=true;
        return $this;
    }
    public function clean($before=null){
        if(!empty($before) && is_callable($before))
            $before($this);   
        foreach ($this->tables as $table => $col) { 
            if($table=='_') continue;
            $col = array_map(function($v){
                list($n)=explode(' ',$v);
                return $n;
            },$col); 
            $result = array_column($this->_query("show columns from $table"),0);  
            foreach ($result as $isset) { 
                if(!in_array($isset,$col)){
                    $str= "alter table {$table} drop {$isset}";
                    $this->_exec($str);
                } 
            }
        } 
        return $this;
    } 
    public function sync($before=null){
        if(!empty($before) && is_callable($before))
            $before($this);    
        $tables = array_column($this->_query('show tables'),0);  
        foreach ($this->tables as $table => $col) {
            if($table=='_')continue;
            if(in_array($this->prefix.$table,$tables))
                $this->_alterTable($table,$col); 
            else
                $this->_createTable($table,$col);
        } 
        return $this;
    }   
    public function truncate($before=null){
        if(!empty($before) && is_callable($before))
            $before($this);   
        foreach ($this->tables as $table => $col) {  
            if($table=='_')  continue;
            $this->_exec("truncate $table");
        } 
        return $this; 
    } 
    public function export(){
        return var_export($this->tables,true); 
    }
   
    function _query($sql,$args=array()){
        return $this->_exec($sql,$args)->fetchAll(); 
    }
    
    public $log=array();
    function _exec($sql,$args=array(),$check=false){ 
        $pdo = $this->pdo;
        $pf  = $this->prefix;   
        $sql = preg_replace(
                array('/((?:join|truncate|into|from|create table|alter table|as)\s+)([\w]+)/' ,
                        '/(\w+)\s+(read|write|set)/',
                        '(\w+\.[\w\*]+)'),
                array("$1 `$pf$2`","`$pf$1` $2" ,"`$pf$0`"), $sql );    
                
        $this->log[]=$sql;
        if($check) return true; 
        $query = $pdo->prepare($sql); 
        $res = $query->execute($args);   
        if(empty($res)) {
            list($c,$n,$d)=$query->errorInfo();
            throw new Exception("DBMigrate Error:".$d, 1); 
        }  
        return $query;  
    } 
    function _createTable($table,$column ){   
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
        return $this->_exec($str,array(),$this->ischeck);  
    }
    function _alterTable($table,$column ){   
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
                    $this->_exec($str,array(),$this->ischeck);   
                } 
                continue;
            }
            list($name,$type) = explode(' ',$value);   
            if( isset($fns[$name]) ){  
                if( !strstr($tps[$fns[$name]],$type) ){ 
                    $str = "alter table {$table} change $name $value; -- old: {$tps[$fns[$name]]}";  
                    $this->_exec($str,array(),$this->ischeck);  
                }
            }else{
                $str = "alter table {$table} add  $value; ";  
                $this->_exec($str,array(),$this->ischeck);  
            } 
        }  
    } 
 
}
