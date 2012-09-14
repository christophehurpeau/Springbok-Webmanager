<?php
class ACSqlDbTable extends CTable{
	public static function create($query,$dbSchema=null){
		return new ACSqlDbTable($query,$dbSchema);
	}
	
	private $dbSchema,$primaryKeys;
	
	public function __construct($query,$dbSchema=null){
		/* DEV */if(!($query instanceof QFindAll || $query instanceof QSql)) throw new Exception('Your query must be an instance of QFindAll'); /* /DEV */
		$this->query=&$query;
		$this->dbSchema=&$dbSchema;
	}
	
	public function execute(){
		if($this->executed===true) return; $this->executed=true;
		$this->pagination=CPagination::_create($this->query)->pageSize(25)->execute($this);
		if($this->filter === 0 && $this->filter && empty($_POST)) $this->filter=false;
		
		if($this->pagination->getTotalResults() !== 0 || $this->filter){
			$this->_setFields(null,null);
		}
	}
	
	
	public function _setFields($fields,$fromQuery){
		$this->fields=array();
		$fields=$this->query->getFields();
		if(empty($fields)) return;
		foreach($fields as $key=>$val){
			$this->fields[]=array('title'=>$val['name'],'escape'=>$val['type']==='string');
		}
	}
	
	public function &getPrimaryKeys(){
		if($this->primaryKeys===null) $this->primaryKeys=$this->dbSchema->getPrimaryKeys();
		return $this->primaryKeys;
	}
	public function &getPrimaryKey(){
		return $this->primaryKeys[0];
	}
}
