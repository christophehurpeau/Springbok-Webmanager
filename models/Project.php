<?php
/** @TableAlias('p') */
class Project extends SSqlModel{
	public
		/** @Pk @AutoIncrement @SqlType('INTEGER') @NotNull
		 */ $id,
		/** @SqlType('VARCHAR(200)') @NotNull
		 */ $name,
		/** @SqlType('VARCHAR(255)') @NotNull
		 */ $path;
	
	public static $hasMany=array(
		'ProjectLang',
		'PluginProject'
	);
	public static $hasManyThrough=array('Plugin'=>array('joins'=>'PluginProject'));
	
	private static $_allProjects;
	public static function listAndOpen(){
		if(self::$_allProjects===null){
			self::$_allProjects=self::QAll()->orderBy('name');
			foreach(self::$_allProjects as $p){
				$p->git=$p->openGit();
			}
		}
		return self::$_allProjects;
	}
	
	
	public function path(){
		return CSession::get('workspace')->projects_dir.$this->path;
	}
	public function link(){
		
	}
	
	public function openGit(){
		if(isset($this->git)) return $this->git;
		$path=$this->path();
		try{
			return $this->git=UGit::open($path);
		}catch(Exception $e){
			try{
				return $this->git=UGit::open(rtrim($path,'/').'/src');
			}catch(Exception $e){
				return null;
			}
		}
	}
	/*
	public function fullUrl(){
		$config=$this->path().'/dev/config/_'.ENV.'.php';
	}
	*/
	public function entries(){
		$config=include $this->path().'/src/config/enhance.php';
		return empty($config["entries"])?array():$config["entries"];
	}
	public function envConfig($env){
		$conf=$this->path().'/src/config/_'.$env.'.';
		return file_exists($conf.'yml') ? UFile::getYAML($conf.'yml') : include $conf.'php';
	}
	
	public function entryBaseUrl($env,$entry){
		$envConfig=$this->envConfig($env);
		return rtrim($envConfig['siteUrl'][$entry],'/').'/';
	}
	
	public function checkCli(){
		if(!file_exists(($devconfig=$this->path().'/dev/config/_'.ENV.'.php'))){
			if(!file_exists($dirname=dirname($devconfig))) mkdir($dirname,0775,true);
			file_put_contents($devconfig,'<?php class Config{public static $autoload_default="";}');
		}
		if(!file_exists(($devconfig=$this->path().'/dev/config/routes.php')))
			file_put_contents($devconfig,'<?php return null;');
		if(!file_exists(($clipath=$this->path().'/cli.php')))
			file_put_contents($clipath,$this->baseDefine()."\n".'$action'."=".'$argv[1];'."\ninclude CORE.'cli.php';");
	}
	
	private function baseDefine(){
		return "<?php
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".dirname(CORE).'/dev/'."');
define('CLIBS','".dirname(CORE)."/libs/dev/');
define('APP', __DIR__.'/dev/');";
	}
}