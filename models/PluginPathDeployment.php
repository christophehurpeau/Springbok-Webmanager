<?php
/** @TableAlias('ppd') */
class PluginPathDeployment extends SSqlModel{
	public
		/** @Pk @AutoIncrement @SqlType('INTEGER') @NotNull
		 */ $id,
		/** @SqlType('INTEGER') @NotNull
		 */ $server_id,
		/** @SqlType('INTEGER') @NotNull
		 */ $plugin_path_id,
		/** @SqlType('VARCHAR(255)') @Null @Default(NULL)
		 */ $folder_name;
	
	public static $belongsTo=array('Server','PluginPath');
	
	public function path($NULL=NULL){
		return $this->server->plugins_dir.$this->folder_name.DS;
	}
}