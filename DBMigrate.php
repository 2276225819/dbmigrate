<?php 
if(!function_exists('array_column')){
    function array_column($arr,$col){  
        return array_map(function($v)use($col){return $v[$col];},$arr);
    }
} 
class DBMigrate {
    private $pdo;
    private $exts;
    private $keys;
    private $column; 
    private $prefix;
    private $ischeck=false;
    public function __construct($pdo,$column=array()){ 
        if( empty($pdo) || !($pdo instanceof \PDO) )
            throw new Exception("DBMigrate Error: PDO Request", 1);
        $this->prefix='';  
        $this->keys= array(
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
        $this->pdo = $pdo;
        $this->column=$column;
    }


    public $last_table='_';
    public $log=array();
    public function __call($name,$args){   
        //laravel  
        if($name=='table'){
            $this->last_table=$args[0]; 
            if(isset($args[1])){  
                if(is_callable($args[1]))$args[1]($this);
                else $this->column[$args[0]] = array_merge(
                    @(array)$this->column[$args[0]], 
                    array_filter(preg_split("/[,\r\n\t][ \r\n\t]+/",$args[1]))
                ); 
            }
            return $this;
        }
        //if(empty($ntable=$this->last_table)){...}
        if($ntable = @$this->last_table );else{
            return $this; 
        } 
        if($name == 'increment' || $name =='increments'){   
            $str = array_shift($args)." int auto_increment primary key";
            $this->column[$ntable][]=$str; 
            return $this;
        }      
        if( isset( $this->keys[$name] ) ){ 
            $str = array_shift($args).' '.$this->keys[$name];
            if($args) $str.='('.join($args,',').')';
            $this->column[$ntable][]=$str; 
            return $this;
        }   
        if( isset( $this->exts[$name] )){
            $index = count($this->column[$ntable])-1; 
            $this->column[$ntable][$index] .= " {$this->exts[$name]} ".var_export($args[0],true); 
            return $this;
        }
    }  
    public function pre($prefix){
        $this->prefix=$prefix;
        return $this;
    }
    public function check(){
        $this->ischeck=true;
        return $this;
    }
    public function key($n,$k){
        if(isset($n)){
            if(is_array($n)) $args = $n;
            else $args = array($n,$k); 
            $this->keys=array_merge($this->keys,$args); 
        } 
        return $this;
    }
    public function clean($before=null){
        if(!empty($before) && is_callable($before))
            $before($this);   
        foreach ($this->column as $table => $col) { 
            if($table=='_')
                continue;
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
        foreach ($this->column as $table => $col) {
            if($table=='_')continue;
            if(in_array($this->prefix.$table,$tables))
                $this->_alterTable($table,$col); 
            else
                $this->_createTable($table,$col);
        } 
        return $this;
    }  
 
    function _query($sql,$args=array()){
        return $this->_exec($sql,$args)->fetchAll(); 
    }
    function _exec($sql,$args=array(),$check=false){ 
        $pdo = @$this->pdo;
        $pf  = @$this->prefix?:'';   
        $sql = preg_replace(
                array('/((?:join|into|from|create table|alter table|as)\s+)([\w]+)/' ,
                        '/(\w+)\s+(read|write|set)/',
                        '(\w+\.[\w\*]+)'),
                array("$1 `$pf$2`","`$pf$0`" ,"`$pf$0`"), $sql );    
                
        @$this->log[]=$sql;
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
        foreach ($column as $key =>$value){
            //preg_match('/[^ ]+(.+)/',$value,$arr); 
            $str.=@$_tag.$value;//arr[1];
            if(empty($_tag)) $_tag=",\n   ";  
        }
        $str.="\n);";    
        return $this->_exec($str,array(),$this->ischeck);  
    }
    function _alterTable($table,$column ){   
        $result = $this->_query("show columns from $table");
        $tps = array_column($result ,1); 
        $fns = array_column($result ,0);
        $fns = array_flip($fns);

        foreach ($column as $value){ 
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
