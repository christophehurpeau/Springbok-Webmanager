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
	
	
	public function doDeployment($workspaceId,$resp=null,$simulation=false,$backup=false,$schema=false){$schema=true;
		$resp=new AHDeploymentResponse($resp);
		/* PROJECT PATH */
		$projectPath=$this->getProjectPath();
		$entrances=$this->project->entrances();
		
		$sshOptions=$this->server->sshOptions();
		
		if(CDaemons::startIfNotAlive('Ssh',$workspaceId.'-'.$this->server_id))
			$resp->push('Daemon started.');
		
		/* DEPLOY CORE */
		$scPath=$this->server->deployCore($this,$resp,$simulation);
		if($scPath===false) return;
		
		/* DO PROJECT DEPLOYMENT */
		if (!$simulation){
			 if ($backup){
			 	$options=array('simulation'=>$simulation,'exclude'=>NULL,'ssh'=>$sshOptions); // --exclude .* ?
			 	$target = $backup.DS;
				$resp->push('BACKUP'.PHP_EOL.UExec::rsync($projectPath,$target,$options));
			 }
		}
		
		$target=$this->path().DS;
		$baseDefine=$this->baseDefine($scPath);

		
		/* -- -- -- */
		
		$options=array('simulation'=>$simulation,'ssh'=>$sshOptions,
				'exclude'=>array('logs/','web/files/*','db','data','.htaccess','authfile','/schema.php','/job.php','/cli.php','/index.php'));
		
		
		$tmpfname = tempnam('/tmp','projectdepl');
		
		file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."='schema';
include CORE.'cli.php';");
		$resp->push('COPY schema.php'.PHP_EOL.UExec::copyFile($tmpfname,$target.'schema.php',$sshOptions));
		
		file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."='job';
include CORE.'cli.php';");
		$resp->push('COPY job.php'.PHP_EOL.UExec::copyFile($tmpfname,$target.'job.php',$sshOptions));
		
		
		file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."=".'$argv[1];'."
include CORE.'cli.php';");
		$resp->push('COPY cli.php'.PHP_EOL.UExec::copyFile($tmpfname,$target.'cli.php',$sshOptions));
		
		$jsFilenames=array('global.js','jsapp.js');
		foreach($this->project->entrances() as $entrance){
			$jsFilenames[]=$entrance.'.js';
			$options['exclude'][]='/'.$entrance.'.php';
		}
		foreach($jsFilenames as $jsfilename){
			if(file_exists($filename=$projectPath.'web/js/'.$jsfilename)){
				$jsFile=file($filename);
				$line0="(function(window,document,Object,Array,Math,undefined){window.basedir='".$this->base_url."';window.webdir=basedir+'web/';window.staticUrl=webdir+'".date('mdH')."/';window.imgdir=webdir+'img/';window.jsdir=webdir+'js/';\n";
				if($jsFile[0]!=$line0){
					$jsFile[0]=$line0;
					file_put_contents($filename,implode('',$jsFile));
				}
			}
		}
		
		$resp->push($this->stop($scPath));
		
		$resp->push('SYNC'.PHP_EOL
		/*$res.=UExec::rsync(dirname(CORE).DS.'prod'.DS,$this->server->core_dir.DS.$sc->path.DS,$options);*/
			.UExec::rsync($projectPath,$target,$options));
		
		$dbPath=$this->project->path().'/db/';
		if(is_dir($dbPath)){
			$options['exclude']=array('.svn/');
			$resp->push('SYNC DB DIR'.PHP_EOL
				.UExec::rsync($dbPath,$target.'db/',$options));
		}
		
		if($schema)
			$resp->push('EXECUTE schema.php'.PHP_EOL
				.UExec::exec('php '.escapeshellarg($target.'schema.php'),$options['ssh']+array('forcePseudoTty'=>true)));
		
		$webFolder=shortAlphaNumber_enc(floor((time()/60-strtotime(date('Y').'-01-01')/60)/3)); //nombres de (3) minutes depuis le début de l'année (2 minutes : on est à 4 lettres à la fin de l'année ; 3 on reste à 3)
		
		$resp->push('CREATE symb link: cd '.escapeshellarg($target.'web/').' && ln -s . "'.$webFolder.'"'.PHP_EOL
			.UExec::exec('cd '.escapeshellarg($target.'web/').' && ln -s . "'.$webFolder.'"',$options['ssh']));
		
		$resp->push('Make sure the rights are good'.PHP_EOL
			.UExec::exec('cd '.escapeshellarg($target.'web/').' && chmod -R --quiet 755 .',$options['ssh']));
		
		$resp->push($this->start($scPath,$webFolder));
		
		$resp->push('Delete CACHE files'.PHP_EOL
			.UExec::exec('cd '.escapeshellarg($target.'data/').' && rm -f cache/* ; rm -f cache/*/* ; rm -f elementsCache/* ; rm -f elementsCache/*/*',$options['ssh']));
		
		
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
				$resp->push('COPY CRON'.PHP_EOL
					.UExec::copyFile($tmpfname,'/etc/cron.d/springbok-'.$this->id,$sshOptions));
			}
		}
		
		if(file_exists($jobFilePath=$projectPath.'jobs/AfterDeployJob.php')){
			$resp->push('EXECUTE job AfterDeploy'.PHP_EOL
				.UExec::exec('php '.escapeshellarg($target.'job.php').' AfterDeploy',$options['ssh']+array('forcePseudoTty'=>true)));
		}
		
		/* Delete old cores */
		$this->server->removeOldCores($resp);
		
		/* Delete tmp file */
		unlink($tmpfname);
	}
	
	
	private function baseDefine($scPath){
		return "
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".$this->server->core_dir.DS.$scPath.DS."');
define('APP', __DIR__.DS);";
	}
	
	/* NEED : project,server */
	public function start($scPath=NULL,$webFolder){
		if($scPath===NULL) throw new Exception("Error Processing Request", 1);
		
		$indexContentStarted="<?php".$this->baseDefine($scPath)."
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
	public function stop($scPath=NULL){
		if($scPath===NULL) throw new Exception("Error Processing Request", 1);
		
		$indexContentStopped="<?php
header('HTTP/1.1 503 Service Temporarily Unavailable',true,503);".$this->baseDefine($scPath)."
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