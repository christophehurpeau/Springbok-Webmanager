<?php
/** @TableAlias('d') */
class Deployment extends SSqlModel{
	public
		/** @Pk @AutoIncrement @SqlType('INTEGER') @NotNull
		 */ $id,
		/** @SqlType('INTEGER') @NotNull
		 * @Index
		 */ $server_id,
		/** @SqlType('INTEGER') @NotNull
		 * @Index
		 */ $project_id,
		/** @SqlType('INTEGER') @Null @Default(NULL)
		 */ $server_core_id,
		/** @SqlType('TEXT') @NotNull
		 */ $path,
		/** @SqlType('TEXT') @NotNull @Default("'/'")
		 */ $base_url,
		/** @SqlType('TEXT') @Null @Default('')
		 */ $ssh;
	
	public static $belongsTo=array('Server','Project');
	
	public function path($NULL=NULL){
		return $this->server->projects_dir.$this->path;
	}
	
	
	public function &getProjectPath(){
		$projectPath=$this->project->path().'/prod/';
		if(!is_dir($projectPath))
			throw new Exception('Project path does not exists: '.$projectPath);
		return $projectPath;
	}
	
	
	public function doDeployment($workspaceId,$simulation=false,$backup=false,$schema=false){$schema=true;
		/* PROJECT PATH */
		$projectPath=$this->getProjectPath();
		$entrances=$this->project->entrances();
		
		$sshOptions=$this->server->sshOptions();
		
		CDaemons::startIfNotAlive('Ssh',$workspaceId.'-'.$this->server_id);
		
		/* DEPLOY CORE */
		//UExec::createPersistantSsh($sshOptions,60);
		$res='=> DEPLOY CORE'.PHP_EOL.$this->server->deployCore($simulation);
		
		/* DO PROJECT DEPLOYMENT */
		if (!$simulation){
			 if ($backup){
			 	$options=array('simulation'=>$simulation,'exclude'=>NULL,'ssh'=>$sshOptions); // --exclude .* ?
			 	$target = $backup.DS;
				UExec::rsync($projectPath,$target,$options);
			 }
		}
		
		$sc=ServerCore::findOneIdAndPathByServer_idAndVersion($this->server_id,Springbok::VERSION);
		
		$target=$this->path().DS;
		$baseDefine=$this->baseDefine($sc);

		
		/* -- -- -- */
		
		$options=array('simulation'=>$simulation,'ssh'=>$sshOptions,
				'exclude'=>array('logs/','web/files/*','db','data','.htaccess','authfile','/schema.php','/job.php','/cli.php','/index.php'));
		
		
		$tmpfname = tempnam('/tmp','projectdepl');
		
		file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."='schema';
include CORE.'cli.php';");
		$res.=PHP_EOL.'=> COPY schema.php'.PHP_EOL;
		$res.=''.UExec::copyFile($tmpfname,$target.'schema.php',$sshOptions);
		
		file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."='job';
include CORE.'cli.php';");
		$res.=PHP_EOL.'=> COPY job.php'.PHP_EOL;
		$res.=''.UExec::copyFile($tmpfname,$target.'job.php',$sshOptions);
		
		
		file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."=".'$argv[1];'."
include CORE.'cli.php';");
		$res.=PHP_EOL.'=> COPY cli.php'.PHP_EOL;
		$res.=''.UExec::copyFile($tmpfname,$target.'cli.php',$sshOptions);
		
		$jsFilenames=array('global.js','jsapp.js');
		foreach($this->project->entrances() as $entrance){
			$jsFilenames[]=$entrance.'.js';
			$options['exclude'][]='/'.$entrance.'.php';
		}
		foreach($jsFilenames as $jsfilename){
			if(file_exists($filename=$projectPath.'web/js/'.$jsfilename)){
				$jsFile=file($filename);
				$line0="var basedir='".$this->base_url."',webdir=basedir+'web/',staticUrl=webdir+'".date('mdH')."/',imgdir=webdir+'img/',jsdir=webdir+'js/';\n";
				if($jsFile[0]!=$line0){
					$jsFile[0]=$line0;
					file_put_contents($filename,implode('',$jsFile));
				}
			}
		}
		
		$res.=PHP_EOL.$this->stop($sc);
		
		$res.=PHP_EOL.'=> SYNC'.PHP_EOL;
		//$res.=UExec::rsync(dirname(CORE).DS.'prod'.DS,$this->server->core_dir.DS.$sc->path.DS,$options);
		$res.=''.UExec::rsync($projectPath,$target,$options);
		
		$dbPath=$this->project->path().DS.'db'.DS;
		if(is_dir($dbPath)){
			$options['exclude']=array('.svn/');
			$res.=PHP_EOL.'=> SYNC DB DIR'.PHP_EOL;
			$res.=''.UExec::rsync($dbPath,$target.'db/',$options);
		}
		
		Deployment::updateOneFieldByPk($this->id,'server_core_id',$sc->id);
		
		if($schema){
			$res.=PHP_EOL.'=> EXECUTE schema.php'.PHP_EOL;
			$res.=''.UExec::exec('php '.escapeshellarg($target.'schema.php'),$options['ssh']+array('forcePseudoTty'=>true));
		}
		
		$res.=PHP_EOL.'=> CREATE symb link : '.'cd '.escapeshellarg($target.'web/').' && ln -s . '.date('mdH').PHP_EOL;
		$res.=''.UExec::exec('cd '.escapeshellarg($target.'web/').' && ln -s . '.date('mdH'),$options['ssh']);
		
		
		$res.=PHP_EOL.'=> Make sure the rights are good'.PHP_EOL;
		$res.=''.UExec::exec('cd '.escapeshellarg($target.'web/').' && chmod -R --quiet 755 .',$options['ssh']);
		
		$res.=PHP_EOL.PHP_EOL.$this->start($sc);
		
		/* UPDATE CRON */
	
		if(false && file_exists($jobsFilePath=$projectPath.'config/jobs.php')){
			$jobs=include $jobsFilePath;
			
			/*
			 * minute (0-59), hour (0-23, 0 = midnight), day (1-31), month (1-12), weekday (0-6, 0 = Sunday), command
			 * x,y = at x and y
			 * x-y = every _ between x and y
			 * * /x = every x _ => * /10 => 0,10,20,30,40,50
			*/
			$cronfile='';
			
			foreach($jobs as $jobName=>$job){
				$cronfile.=$job.' www-data php '.escapeshellarg($target.'job.php').' '.$jobName.PHP_EOL;
			}
			
			if(!empty($cronfile)){
				file_put_contents($tmpfname,$cronfile);
				$res.=PHP_EOL.'=> COPY CRON'.PHP_EOL;
				$res.=''.UExec::copyFile($tmpfname,'/etc/cron.d/springbok-'.$this->id,$sshOptions);
			}
		}
		
		if(file_exists($jobFilePath=$projectPath.'jobs/AfterDeployJob.php')){
			$res.=PHP_EOL.'=> EXECUTE job AfterDeploy'.PHP_EOL;
			$res.=''.UExec::exec('php '.escapeshellarg($target.'job.php').' AfterDeploy',$options['ssh']+array('forcePseudoTty'=>true));
		}
		
		/* Delete old cores */
		$cores=ServerCore::QAll()->with('Deployment',array('isCount'=>true))->with('Server')->having(array('deployments=0'));
		if(!empty($cores)){
			$res.=PHP_EOL.'=> DELETE OLD CORES'.PHP_EOL;
			foreach($cores as $core){
				UExec::exec('rm -rf '.$core->server->core_dir.DS.$core->path,$core->server->sshOptions());
				$res.=$core->path.PHP_EOL;
				$core->delete();
			}
		}
		
		/* Delete tmp file */
		unlink($tmpfname);
		
		return $res;
	}
	
	
	private function baseDefine($sc){
		return "
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".$this->server->core_dir.DS.$sc->path.DS."');
define('APP', __DIR__.DS);";
	}
	
	/* NEED : project,server */
	public function start($sc=NULL){
		if($sc===NULL) $sc=ServerCore::findOneIdAndPathByIdAndServer_id($this->server_core_id,$this->server_id);
		
		$webFolder=date('mdH');
		$indexContentStarted="<?php".$this->baseDefine($sc)."
define('APP_DATE',".time().");define('WEB_FOLDER','".$webFolder."/');
include CORE.'app.php';";

		$tmpfname = tempnam('/tmp','projectstart');
		file_put_contents($tmpfname,$indexContentStarted);
		$entrances=$this->project->entrances();
		$sshOptions=$this->server->sshOptions();
		$target=$this->path().DS;

		$res='=> START PROJECT'.PHP_EOL
			.UExec::copyFile($tmpfname,$target.'index.php',$sshOptions);
		
		if(!empty($entrances))
			foreach($entrances as $entrance)
				$res.=PHP_EOL.'=> START ENTRANCE: '.$entrance.PHP_EOL
					.UExec::copyFile($tmpfname,$target.$entrance.'.php',$sshOptions);
		
		if(file_exists($deamonsFilePath=$this->getProjectPath().'config/daemons.php')){
			$res.=PHP_EOL.'=> START daemons'.PHP_EOL;
			$res.= UExec::exec('php '.escapeshellarg($target.'cli.php').' daemons',$options['ssh']+array('forcePseudoTty'=>true));
		}
		
		unlink($tmpfname);
		return $res;
	}
	
	/* NEED : project,server */
	public function stop($sc=NULL){
		if($sc===NULL) $sc=ServerCore::findOneIdAndPathByIdAndServer_id($this->server_core_id,$this->server_id);
		
		$indexContentStopped="<?php
header('HTTP/1.1 503 Service Temporarily Unavailable',true,503);".$this->baseDefine($sc)."
if(file_exists((".'$filename'."=CORE.'maintenance.php'))){
	define('APP_DATE',".time()."); define('WEB_FOLDER','');
	include ".'$filename'.";
}else echo '<h1>503 Service Temporarily Unavailable</h1>';";

		$tmpfname = tempnam('/tmp','projectstop');
		file_put_contents($tmpfname,$indexContentStopped);
		$entrances=$this->project->entrances();
		$sshOptions=$this->server->sshOptions();
		$target=$this->path().DS;
		
		$res='=> STOP PROJECT'.PHP_EOL
			.UExec::copyFile($tmpfname,$target.'index.php',$sshOptions);
		if(!empty($entrances))
			foreach($entrances as $entrance)
				$res.=PHP_EOL.'=> STOP ENTRANCE: '.$entrance.PHP_EOL
					.UExec::copyFile($tmpfname,$target.$entrance.'.php',$sshOptions);
		
		unlink($tmpfname);
		
		if(file_exists($deamonsFilePath=$this->getProjectPath().'config/daemons.php')){
			$res.=PHP_EOL.'=> KILL DAEMONS: '.$entrance.PHP_EOL.UExec::exec('killall php',$sshOptions);
		}
		
		return $res;
	}
}