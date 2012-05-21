<?php
class AController extends Controller{
	/** @var Workspace */
	public static $workspace;
	
	public static function beforeDispatch(){
		if(!CSession::exists('workspace')){
			if(CCookie::exists('Workspace')){
				$workspaceCookie=CCookie::get('Workspace');
				if(empty($workspaceCookie->id)){
					if(get_called_class() ==='WorkspaceController') return;
					self::redirect('/workspace');
				}else{
					$workspace=Workspace::findOneById($workspaceCookie->id);
					if(empty($workspace)) self::redirect('/workspace');
					CSession::set('workspace',$workspace);
				}
			}elseif(get_called_class() ==='WorkspaceController') return;
			else self::redirect('/workspace');
		}
		self::$workspace=CSession::get('workspace');
		SSqlModel::$__dbName=self::$workspace->db_name;
		SSqlModel::$__modelDb=DB::init(SSqlModel::$__dbName);
		/* DEV */
		$schemaProcessing=new DBSchemaProcessing(new Folder(APP.'models'),new Folder(APP.'triggers'));
		/* /DEV */
	}
	
	/**
	 * @return DBSQLite
	 */
	protected static function loadCoreDB($lang){
		return new DBSQLite(false,array(
			'file'=>dirname(CORE).DS.'langs'.DS.$lang.'.db'
		));
	}
	
	
	protected static function _recursiveFiles(&$path,&$arrayStrings,$functionName='_t',$deleteLastParam=false,$pattern=false){
		foreach(new RecursiveDirectoryIterator($path,FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS)
					as $pathname=>$fileInfo){
			if(substr($fileInfo->getFilename(),0,1) == '.') continue;
			if($fileInfo->isDir()) self::_recursiveFiles($pathname,$arrayStrings,$functionName,$deleteLastParam);
			if(!in_array(substr($fileInfo->getFilename(),-3),array('.js','php')) || substr($fileInfo->getFilename(),0,4)=='i18n') continue;
			$matches=array(); preg_match_all($pattern?$pattern:'/(?:\b'.$functionName.'\((.+)\)|\{'.substr($functionName,1).'\s+([^}]+)\s*\})/Um',file_get_contents($pathname),$matches);
			if(!empty($matches[1])){
				foreach($matches[1] as $key=>$value)
					if(empty($matches[1][$key])) $matches[1][$key]=$matches[2][$key];
				unset($matches[2]);
				
				$matches=array_map(function($v) use(&$deleteLastParam){
					$string=substr($v,1);
					if($deleteLastParam) $string=substr($string,0,strrpos($string,','));
					return stripslashes(substr($string,0,-1));
				},$matches[1]);
				$arrayStrings['all']=array_merge($arrayStrings['all'],$matches);
				$arrayStrings[$pathname]=$matches;
			}
		}
	}
	
}
