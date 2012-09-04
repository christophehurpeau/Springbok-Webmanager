<?php
/** @TableAlias('pd') */
class PluginDeployment extends SSqlModel{
	public
		/** @Pk @AutoIncrement @SqlType('INTEGER') @NotNull
		 */ $id,
		/** @SqlType('INTEGER') @NotNull
		 */ $server_id,
		/** @SqlType('INTEGER') @NotNull
		 */ $plugin_id,
		/** @SqlType('INTEGER') @Null @Default(NULL)
		 */ $server_plugin_id;
	
	public static $belongsTo=array(
		'Server','Plugin',
		'PluginPathDeployment'=>array('onConditions'=>array('pd.server_id=ppd.server_id','plg.plugin_path_id=ppd.plugin_path_id'),'foreignKey'=>false),
	);
	
	public function path($NULL=NULL){
		return $this->server->plugins_dir.$this->pathDeployment->folder_name.DS.$this->plugin->folder_name.DS;
	}
	
	public function deploy(){
		throw new Exception('Il ne peut pas y avoir de versions de plugins : le schéma doit rester identique et à jour.'); 
		/* PLUGIN PATH */
		$pluginPath=$this->plugin->path().DS.'prod'.DS;
		
		if(!is_dir($pluginPath))
			throw new Exception('Plugin path does not exists: '.$pluginPath);
		
		$devConfig=include $pluginPath.'config/_'.ENV.'.php';
		$version=$devConfig['enhance_time'];
		$sp=ServerPlugin::findOneIdAndFolder_nameByServer_idAndPlugin_idAndVersion($this->server_id,$this->plugin_id,$version);
		if(empty($sp)){
			$sp_folderName=$this->plugin->folder_name.'-'.date('Y-m-d');
			if($id=ServerPlugin::findValueIdByServer_idAndPlugin_idAndFolder_name($this->server_id,$sp_folderName))
				ServerCore::updateOneFieldByPk($id,'version',$version);
			else{
				$sp=new ServerPlugin();
				$sp->server_id=$this->server_id;
				$sp->plugin_id=$this->plugin_id;
				$sp->version=Springbok::VERSION;
				$sp->folder_name=$sp_folderName;
				$sp->insert();
			}
		}else $res='This plugin is already up-to-date'.PHP_EOL;
	}
	
	/** Need : server,plugin=>{with:PluginPath},pluginPathDeployment */
	public function doDeployment($workspaceId,$simulation=false,$backup=false,$schema=false){$schema=true;
		throw new Exception();
		/* LINKED PROJECTS */
		$linkedProjectsDeployments=Deployment::QAll()
			->with('Project',array('with'=>array('PluginProject'=>array('fields'=>false,'join'=>true))))
			->where(array('server_id'=>$this->server_id,'plgPrj.plugin_id'=>$this->plugin_id))
			->orderBy(array('plgPrj.position'));

		if(empty($linkedProjectsDeployments))
			throw new Exception('No linked projects for this server !!');
		
		/* PLUGIN PATH */
		$pluginPath=$this->plugin->path().DS.'prod'.DS;
		
		if(!is_dir($pluginPath))
			throw new Exception('Plugin path does not exists: '.$pluginPath);
		
		/* CHECK LINKED PROJECTS PATH */
		foreach($linkedProjectsDeployments as &$depl) $depl->getProjectPath();
		
		
		/* DEPLOY CORE */
		throw new Exception;
		$scPath=$this->server->deployCore(false,$resp);
		$res='========== DEPLOY CORE =========='.PHP_EOL.$resDeplCore;
		
		/* STOP ALL LINKED PROJECTS */
		foreach($linkedProjectsDeployments as &$depl){
			$depl->_set('server',$this->server);
			$res.=PHP_EOL.PHP_EOL.'========== STOP PROJECT: '.$depl->project->name.' =========='.PHP_EOL.$depl->stop($scPath);
		}

		/* DEPLOY PLUGIN */
		$simulation=false;
		$options=array('simulation'=>$simulation,'ssh'=>$this->server->sshOptions());
		$res.=PHP_EOL.PHP_EOL.'========== SYNC PLUGIN =========='.PHP_EOL;
		$res.=UExec::rsync($pluginPath,$this->path(),$options);

		$baseDefine=$this->baseDefine($scPath);
		$tmpfname = tempnam('/tmp','plugindepl');
		$target=$this->path();
		
		if($schema){
			$res.=PHP_EOL.PHP_EOL.'========== SCHEMA PLUGIN =========='.PHP_EOL;
			file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."='schema';
include CORE.'plugin.php';");
			$res.=PHP_EOL.'=> COPY schema.php'.PHP_EOL;
			$res.= UExec::copyFile($tmpfname,$target.'schema.php',$options['ssh']);
		}
		
		file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."=".'$argv[1];'."
include CORE.'plugin.php';");
		$res.=PHP_EOL.PHP_EOL.'=> COPY cli.php'.PHP_EOL;
		$res.= UExec::copyFile($tmpfname,$target.'cli.php',$options['ssh']);
		
		
		if($schema){
			$res.=PHP_EOL.PHP_EOL.'=> EXECUTE schema.php'.PHP_EOL;
			$res.= UExec::exec('php '.escapeshellarg($target.'schema.php'),$options['ssh']+array('forcePseudoTty'=>true));
		}
		
		/* DEPLOY ALL PROJECTS */
		foreach($linkedProjectsDeployments as &$depl)
			$res.=PHP_EOL.PHP_EOL.'========== DEPLOY PROJECT: '.$depl->project->name.' =========='.PHP_EOL.$depl->doDeployment($workspaceId,$resp,$simulation,$backup,$schema);
		
		return $res;
	}

/*
	private function baseDefine($scPath){
		return "
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".$this->server->core_dir.DS.$scPath.DS."');
define('APP', __DIR__.DS);";
	}*/
}