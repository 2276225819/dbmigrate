<?php
class Model implements ArrayAccess 
{   
	public $dirty=[];
	public $data=[];
    public function offsetSet($offset, $value)
    {
		$this->dirty[$offset]=$value;
    } 
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]) ||isset($this->dirty[$offset]);
    } 
    public function offsetUnset($offset)
    {
        $this->dirty[$offset]=null;
    } 
    public function offsetGet($offset)
    {
		return $this->dirty[$offset]??$this->data[$offset];
    }


    public function __construct($data=[],Query $sql=null)
    {
		if(!empty($sql)) 
			$this->query = $sql; 
		foreach ($data as $key => $value)
			$this->data[$key]=$value; 
 
    }
	public static function all($key=null,$val=null):Query{
		return new Query(get_called_class(),true);
	}

	public static function load(...$pks)
	{
		$query = new Query(get_called_class(),false);
		$arr = array_combine((array)static::$pk,$pks); 
		return $query->and($arr)->fetch();
	}
    public function save($data=null)
    {

    }



	public function hasOne($model,$pk=['id']):Model{
		$query = new Query($model,false);
		$arr = array_combine((array)$query->getPK(),[$this[static::$pk]]); 
		return $query->and($arr)->fetch(); 
	}
	public function hasMary($model):Query{

	}
}

class Dk extends Model{
	public static $pk='ID';
	public static $table="d_device";
}
class Us extends Model{
	public static $pk='ID';
	public static $table="u_user";
}


include "Query.php";
Query::config("mysql:dbname=test",'root','root');


$dk = new Dk(['cc'=>2]);
$dk['aa']=10;
$dk->save();

foreach(Dk::all() as $row){
	print_r($row->hasOne(Us::class));
}
 
print_r($dk);
exit;

$dks = Dk::all();
if(true)$dks->and("a=?",4);
if(true)$dks->and(['a'=>4]);
if(true)$dks->order("aa");
$dk = $dks->fetch();

$dk = Dk::insert([]);

foreach (Dk::all('id') as $id=>$dk) { 
	$user = $dk->hasOne('user');
}