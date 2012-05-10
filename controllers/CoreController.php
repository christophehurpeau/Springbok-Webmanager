<?php
class CoreController extends AController{
	/** */
	function index(){
		self::set('current_version',Springbok::VERSION);
		self::render();
	}
	
	/** */
	function langs(){
		$langs=ProjectLang::QValues()->fields('DISTINCT name');
		self::mset($langs);
		self::render();
	}
	
	/**
	 * lang > @Required
	 */
	function lang($lang){
		if(CValidation::hasErrors()) notFound();
		$db=self::loadCoreDB($lang);
		$modelInfos=array(
			'primaryKeys'=>array('s'),
			'columns'=>array(
				's'=>array('type'=>'TEXT','notnull'=>true,'unique'=>false,'default'=>false),
				't'=>array('type'=>'TEXT','notnull'=>true,'unique'=>false,'default'=>false)
			)
		);
		$tableName='t';
		$dbSchema=DBSchema::get($db,$tableName);
		$dbSchema->setModelInfos($modelInfos);
		if(!$dbSchema->tableExist('t')) $dbSchema->createTable();
		//else $dbSchema->compareTableAndApply();
		
		$corePath=dirname(CORE).DS.'src';
		
		$arrayStrings=array('all'=>array());
		self::_recursiveFiles($corePath,$arrayStrings,'_tC');
		$all=array_unique($arrayStrings['all']);
		unset($arrayStrings['all']);
		//exit;
		
		self::set('translations',$db->doSelectListValue('SELECT s,t FROM t'));
		
		self::set_('allStrings',$all);
		self::mset($lang,$arrayStrings);
		self::render();
	}
	
	
	/**
	 * lang > @Required
	 */
	function lang_save($lang, array $data){
		if(CValidation::hasErrors()) notFound();
		$db=self::loadCoreDB($lang);
		$db->doUpdate('DELETE FROM t');
		$statement=$db->prepare('INSERT INTO t(s,t) VALUES (:s,:t)');
		foreach($data as $s=>$t){
			$statement->bindValue(':s',$s);
			$statement->bindValue(':t',$t);
			$statement->execute();
		}
		
		file_put_contents(dirname(CORE).'/src/i18n/langs/'.$lang.'.php','<?php return '.UPhp::exportCode($data).';');
		
		$content="window.i18nc={";
		foreach($data as $s=>$t)
			$content.=UPhp::exportString($s).':'.UPhp::exportString($t).',';
		file_put_contents(dirname(CORE).'/src/includes/js/langs/core-'.$lang.'.js',substr($content,0,-1)."};");
		
		self::redirect('/core/lang/'.$lang);
	}
	
}
