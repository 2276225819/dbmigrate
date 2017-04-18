<?php

class Query implements Iterator
{ 
	public static $currDB;
    public static function config($dns, $name=null, $pass=null)
    {
		static::$currDB=new \PDO($dns,$name,$pass,[
			PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
		]);
    }
    public function __construct(string $model=null,$mulit=true)
    {
        $this->pdo=static::$currDB;
		$this->mulit=$mulit; 
		$this->tname=$model;
		if(class_exists($model)){
			$this->tname = $model::$table??$model;
			$this->model = $model;
		}
    }
	public function getPK(){
		if(isset($this->model))
			return $this->model::$pk;
		return 'id';
	}
	public $pdo;
	public $mulit;
	public $query;
	public $tname;
    public $param=[];
    public $field='*';
    public $where;
    public $order;
    public $limit;
    public function fetch():Model
    { 
		if(empty($this->model))
			throw new Exception("Error Processing Request", 1);
			
		if($data = $this->fetchRaw());else 
			throw new Exception("Error Processing Request", 1);
		return new $this->model($data,$this);
    }
	public function insert($data,$arr=null):Model
	{ 
		if(empty($this->model))
			throw new Exception("Error Processing Request", 1);
		$data = $this->insertRaw($data,$arr);
		return new $this->model($this,$data); 
	}
	public function fetchRaw()
	{
		if(empty($this->query))
			if(!$this->execute("SELECT {$this->field} FROM {$this->tname} {$this->where} {$this->order} {$this->limit}",$this->param))
				throw new Exception("Error Processing Request", 1);
				
		return $this->query->fetch();
	}
    public function insertRaw($data, $arr = null)
    {
        $sql="INSERT INTO {$this->tname} SET ".$this->kvSQL(',', $data, $arr);
        if (!$this->execute($sql, $this->param)) {
            throw new Exception("Error Processing Request", 1);
        }
        if ($ai = $model->getAI()) {
            $data[$ai]=$this->pdo->lastInsertId();
        }
        return $data;
    }
    public function insertMulit($args) :bool
    {
        $sql1 = "";
        $sql2 = "";
        foreach ($args[0] as $key => $value) {
            $sql1.=",`{$key}`";
        }
        foreach ($args as $arr) {
            $sql2.=",(".substr(str_repeat(",?", count($arr)), 1).")";
            array_push($this->param, ...array_values($arr));
        }
        $sql="INSERT INTO {$this->tname} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        return !!$this->execute($sql, $this->param);
    }
    public function update($data, $arr = null) :bool
    {
        if (empty($where)) {
			throw new Exception("Error Processing Request", 1);
        }
        $data=$this->kvSQL(',', $data, $arr);
        $sql="UPDATE {$this->tname} SET {$data} {$this->where}";
        return !!$this->execute($sql, $this->param);
    }
    public function delete() :bool
    {
        if (empty($where)) {
            return false;
        }
        $sql="DELETE FROM {$this->tname} {$this->where}";
        return !!$this->execute($sql, $this->param);
    }
    public function execute($sql, $args = []):bool
    {
        $pf  = $this->prefix??'';
        $sql = preg_replace(
                array('/((?:join|truncate|into|from|create table|alter table|as)\s+)([\w]+)/' ,
                        '/(\w+)\s+(read|write|set)/',  '(\w+\.[\w\*]+)'),
                array("$1 `$pf$2`","`$pf$1` $2" ,"`$pf$0`"), $sql );
        $query = $this->query = $this->pdo->prepare($sql);
        return $query->execute($args); 
    }

    public function limit($limit, $offset = 0)
    {
        $this->limit=" LIMIT $offset,$limit";
    }
    public function order($order) :Query
    {
        $this->order=" ORDER BY ".$order;
        return $this;
    }
    public function field($fields) :Query
    {
        $this->field=$this->kvSQL(',', $fields);
        return $this;
    }
    public function where($w, $arr = null) :Query
    {
        $this->where=' WHERE '.$this->kvSQL('AND', $w, $arr);
        return $this;
    }
    public function and($w, $arr = null) :Query
    {
        $this->where.=empty($this->where)?" WHERE ":" AND ";
        $this->where.=$this->kvSQL('AND', $w, $arr);
        return $this;
    }
    public function or($w, $arr = null) :Query
    {
        $this->where.=empty($this->where)?" WHERE ":" OR ";
        $this->where.=$this->kvSQL('OR', $w, $arr);
        return $this;
    }

    function kvSQL($join = 'AND', $arr, $attr = null, $sql = ''):string
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $v) {
                if (is_array($v)) {
                    $str= substr(str_repeat(",?", count($arr)), 1);
                    $sql.="{$join} {$key} in ($str)";
                    $this->param=array_merge($this->param, $v);
                } else {
                    $sql.= "{$join} {$key}=?";
                    $this->param[]=$v;
                }
            }
            $sql=substr($sql, strlen($join));
        } else {
            $sql=$arr;
            if (is_array($attr)) {
                $this->param=array_merge($this->param, $attr);
            }
        }
        return $sql;
    }


	public $array;
	function rewind() {
		if(!$this->execute("SELECT {$this->field} FROM {$this->tname} {$this->where} {$this->order} {$this->limit}",$this->param))
			throw new Exception("Error Processing Request", 1); 
		$this->array=$this->query->fetchAll();
    } 
    function current(){	
		$data = current($this->array);  
		return empty($this->model)?$data:new $this->model($data,$this);
    } 
    function key() {
        return key($this->array);
    } 
    function next() {
		return next($this->array); 
    } 
    function valid() {
		return !!current($this->array); 
    }
}
