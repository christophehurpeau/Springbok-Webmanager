<?php
class AHSqlTable extends AHDbTable{
	private static $url,$qsql;
	
	public static function table(CTable $component,$url=null,QSql $qsql=null){
		self::$url=&$url;
		self::$qsql=&$qsql;
		parent::table($component);
		if($component->getResults() !=0) $fields=array_map(function(&$field){return $field['name'];},$qsql->getFields());
		//debugVar($qsql->getFields());
	}
	
	protected static function displayResults(&$component,&$results){
		$pkName=count($component->getPrimaryKeys())===1?$component->getPrimaryKey():false;
		foreach($results as $key=>&$row){
			echo '<tr';
			//array('/database/:dbid/:dbname/:action/*',$database->id,$dbname,'table','/'.$tablename)
			if($pkName!==false){
				$url=self::$url;
				$iRow=array_search($pkName,array_map(function(&$field){return $field['name'];},self::$qsql->getFields()));
				$url[4].=$row[$iRow];
				//echo ' class="pointer" onclick="S.redirect(\''.HHtml::urlEscape($url).'\')"';
			}else $iRow=-1;
			echo '>';
			foreach($component->fields as $i=>$field){
				//$value=$row[$field['title']];
				$escape=true;
				$value=$row[$i];
				if($i===$iRow){
					$value='<a href="'.HHtml::urlEscape($url).'">'.$value.'</a>';
					$escape=false;
				}
				self::displayValue($field,$value,$model,$escape);
			}
		}
	}
}
