<?php 

class TableBlock{
 
    public $name="";//create table ""
    public $attr="";//ENGINE="",AUTO_INCREMENT="",CHARSET=""
    public $index=[];//UNIQUE KEY `` (``)
    public $cols=[];//["id int ","name varchar"]
    public function __construct($name,$cols,$attr){ 
        $this->name = $name;
        $this->attr = trim($attr); 
        foreach ($cols as $str)  {
            if($str = trim($str));else
                continue;
            if(strtolower(substr($str,0,11))=="primary key"
            || strtolower(substr($str,0,10))=="unique key"
            || strtolower(substr($str,0,3))=="key" ){ 
                if(!in_array($str,$this->index)) 
                    $this->index[]=$str;   
            }else{
                $of = preg_match('/^`?(\w+)`? /',$str,$arr);
                if(empty($arr[1]))
                    throw new Exception("Error Processing Request", 1); 
                preg_match_all('/[a-z0-9_]+\(\d+,\d+\)|[a-z0-9_]+\(\d+\)|NOT NULL|DEFAULT \S+|COMMENT \S+|AUTO_INCREMENT|UNSIGNED|[a-z0-9_]+/i',$str,$attr,0,$of+strlen($arr[1]));  
                $this->cols[$arr[1]] = array_merge($this->cols[$arr[1]]??[],$attr[0]);  
            }  
        } 
    }   
    public function __toString(){
        $str=""; 
        foreach ($this->cols as $key => $value) {
            $str.=",\n    $key ".join($value,' ');
        }    
        foreach ($this->index as $value) {
            $str.=",\n  $value";
        }    
        return "create table {$this->name}(".substr($str,1)."\n) {$this->attr}";
    }
    public static function read($str,&$offset=0)  { 
        $offset += preg_match("/create\s*table\s*`?(\w+)`?\s*\(([^;]*)\)([^;]*)/i",$str,$arr,0,$offset);  
        if(empty($arr)) return; 

        $offset += strlen($arr[0]) ; 
        $arr[2]=preg_split('/,\s/',$arr[2]);
        foreach ($arr[2] as $key => $value)
            $cols[] = trim($value);  
        return new self($arr[1],$cols,$arr[3]); 
    }

    public function memgeTable(TableBlock $table){ 
        $this->cols=array_merge_recursive($this->cols,$table->cols);
        $this->index=array_merge_recursive($this->index,$table->index); 
        $this->index=array_unique($this->index);
        foreach ($this->cols as &$value) {
            $value=array_unique($value); 
        }
    }
    public function diffTable(TableBlock $table){


    }  
    


}