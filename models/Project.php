<?php
/**
 * @TableAlias('p')
 */
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
	
	
	public function path(){
		return CSession::get('workspace')->projects_dir.$this->path;
	}
	
	public function entries(){
		$config=include $this->path().'/src/config/enhance.php';
		return empty($config["entries"])?array():$config["entries"];
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
define('APP', __DIR__.'/dev/');";
	}
}