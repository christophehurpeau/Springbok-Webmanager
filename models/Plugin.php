<?php
/** @TableAlias('plg') */
class Plugin extends SSqlModel{
	public
		/** @Pk @AutoIncrement @SqlType('INTEGER') @NotNull
		 */ $id,
		/** @SqlType('VARCHAR(200)') @NotNull
		 */ $name,
		/** @SqlType('VARCHAR(200)') @NotNull
		 */ $folder_name,
		/** @SqlType('VARCHAR(255)') @NotNull
		 */ $plugin_path_id;
	
	public static $belongsTo=array(
		'PluginPath',
	);
	
	public static $hasMany=array(
		'PluginProject'
	);
	public static $hasManyThrough=array('Project'=>array('joins'=>'PluginProject'));
	
	public function path($NULL=NULL){
		return $this->path->path.DS.$this->folder_name;
	}
}