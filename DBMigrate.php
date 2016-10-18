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
    public function __construct($pdo,$column=array()){ 
        if( empty($pdo) || empty($pdo instanceof \PDO) )
            throw new Exception("PDO Request", 1);
            
        $this->keys= array(
            'int','text','date','time','datetime','tinyint',
            'varchar','char',  'decimal','double', 
        ); 
        $this->exts = array('comment','default');
        $this->pdo = $pdo;
        $this->column=$column;
    }


    public $last_table='_';
    public $log;
    public function __call($name,$args){  
        if($name=='table'){
            $this->last_table=$args[0]; 
            if(isset($args[1])){  
                $this->column[$args[0]] = array_merge(
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
        if($name == 'increment'){   
            $str = array_shift($args)." int auto_increment primary key";
            $this->column[$ntable][]=$str; 
            return $this;
        }      
        if( in_array($name,$this->keys) ){ 
            $str = array_shift($args).' '.$name;
            if($args) $str.='('.join($args,',').')';
            $this->column[$ntable][]=$str; 
            return $this;
        }   
        if( in_array($name,$this->exts) ){
            $index = count($this->column[$ntable])-1; 
            $this->column[$ntable][$index] .= " {$name} ".var_export($args[0],true); 
            return $this;
        }
    } 
    
    public function clean($before=null){
        if(!empty($before) && is_callable($before))
            $before($this);   
        foreach ($this->column as $table => $col) { 
            $col = array_map(function($v){
                list($n)=explode(' ',$v);
                return $n;
            },$col); 
            $result = array_column($this->query("desc $table"),0);  
            foreach ($result as $isset) { 
                if(!in_array($isset,$col)){
                    $str= "alter table {$table} drop {$isset}";
                    $this->exec($str);
                } 
            }
        } 
        return $this;
    } 
    public function sync($before=null){
        if(!empty($before) && is_callable($before))
            $before($this);    
        $tables = array_column($this->query('show tables'),0); 
        foreach ($this->column as $table => $col) {
            if(in_array($table,$tables))
                $this->alterTable($table,$col); 
            else
                $this->createTable($table,$col);
        } 
        return $this;
    }  
 
    function query($sql,$args=array()){
        return $this->exec($sql,$args)->fetchAll(); 
    }
    function exec($sql,$args=array()){ 
        $pdo = @$this->pdo;
        $pf  = @$this->prefix?:'';   
        $sql = preg_replace(
                array('/((?:join|into|from|create table|alter table|as)\s+)([\w]+)/' ,
                        '/(\w+)\s+(read|write|set)/',
                        '(\w+\.[\w\*]+)'),
                array("$1 $pf$2","$pf$0" ,"$pf$0"), $sql );    
        $query = $pdo->prepare($sql); 
        $res = $query->execute($args);  
        @$this->log[]=$sql; 
        if(empty($res)) {
            list($c,$n,$d)=$query->errorInfo();
            throw new Exception($d, 1); 
        }  
        return $query; 
    } 
    function createTable($table,$column ){   
        $str = "create table {$table}(\n   ";
        foreach ($column as $key =>$value){
            //preg_match('/[^ ]+(.+)/',$value,$arr); 
            $str.=@$_tag.$value;//arr[1];
            if(empty($_tag)) $_tag=",\n   ";  
        }
        $str.="\n);";    
        return $this->exec($str);  
    }
    function alterTable($table,$column ){   
        $result = $this->query("desc $table");
        $tps = array_column($result ,1); 
        $fns = array_column($result ,0);
        $fns = array_flip($fns);

        foreach ($column as $value){ 
            list($name,$type) = explode(' ',$value);   
            if( isset($fns[$name]) ){  
                if( !strstr($tps[$fns[$name]],$type) ){ 
                    $str = "alter table {$table} change $name $value;\n";  
                    $this->exec($str);
                }
            }else{
                $str = "alter table {$table} add  $value;\n";  
                $this->exec($str);
            } 
        }  
    } 
}
