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
	
	public function name(){
		return $this->server->host.':'.$this->path();
	}
	
	
	public function getProjectPath(){
		$projectPath=$this->project->path().'/prod/';
		if(!is_dir($projectPath))
			throw new Exception('Project path does not exists: '.$projectPath);
		return $projectPath;
	}
	
	
	public function doDeployment($workspaceId,$resp=null,$simulation=false,$backup=false,$schema=false){$schema=true;
		$resp=new AHDeploymentResponse($resp);
		/* PROJECT PATH */
		$projectBasePath=rtrim($this->project->path(),'/').'/';
		$projectPath=$this->getProjectPath();
		$entries=$this->project->entries();
		$envConfig=$this->project->envConfig($this->server->env_name);
		$target=rtrim($this->path(),'/').DS;
		
		$resp->push('Hi ! Deployment : '.$projectBasePath.' ===> '.$this->server->host.':'.$target);
		
		/* Pre - deployment */
		// REQUIRED : pre-dbprocessing by PROD (use APP instead of dirname(APP))
		copy($projectBasePath.'currentDbVersion',$projectPath.'currentDbVersion');
		
		$baseDefine="<?php
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".dirname(CORE)."/prod/');
define('CLIBS','".dirname(CORE)."/libs/prod/');
define('APP', __DIR__.DS);";

		$schemaContent=$baseDefine."
".'$action'."='schema';
include CORE.'cli.php';";
		file_put_contents($projectPath.'schema.php',$schemaContent);
		
		$resp->push($resSchemaProcess=UExec::exec('php '.escapeshellarg($projectPath.'schema.php')));
		if('Schema processed'!==substr($resSchemaProcess,-strlen('Schema processed'))) return false;
		
		/* Prepare SSH */
		$sshOptions=$this->server->sshOptions();
		
		$resp->push($this->stopDaemon($workspaceId));
		$resp->push($this->startDaemon($workspaceId));
		
		// Get current db version
		$currentLocalDbVersion=UFile::getContents($projectBasePath.'currentDbVersion');
		$currentServerDbVersion=UExec::exec('cd / && cat '.escapeshellarg($target.'currentDbVersion'),$sshOptions);
		$resp->push('DB Versions : server='.$currentServerDbVersion.', local='.$currentLocalDbVersion);
		$stopProject=$currentServerDbVersion != $currentLocalDbVersion || (isset($_REQUEST['projectStop']) && $_REQUEST['projectStop']=='1');
		$resp->push('stop : '.($stopProject?'true':'false'));
		
		/* DEPLOY CORE */
		$scPath=$this->server->deployCore($this,$resp,$simulation);
		if($scPath===false) return;
		
		$resp->push($this->stopDaemon($workspaceId));
		$resp->push($this->startDaemon($workspaceId));
		
		/* DO PROJECT DEPLOYMENT */
		if (!$simulation){
			 if ($backup){
			 	$options=array('simulation'=>$simulation,'exclude'=>NULL,'ssh'=>$sshOptions); // --exclude .* ?
			 	$targetBackup = $backup.DS;
				$resp->push('BACKUP'.PHP_EOL.UExec::rsync($projectPath,$targetBackup,$options));
			 }
		}
		
		$baseDefine=$this->baseDefine($scPath);

		
		/* -- -- -- */
		
		$options=array('simulation'=>$simulation,'ssh'=>$sshOptions,'exclude'=>array());
		
		
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
".'unset($argv[0]); $action'."=".'array_shift($argv);'."
include CORE.'cli.php';");
		$resp->push('COPY cli.php'.PHP_EOL.UExec::copyFile($tmpfname,$target.'cli.php',$sshOptions));
		
		
		
		/* Prepare content */
		
		$lastWebFolder=UExec::exec('cd / && cat '.escapeshellarg($target.'lastWebFolder'),$sshOptions);
		
		$webFolder=shortAlphaNumber_enc(floor((time()/60-strtotime(date('Y').'-01-01')/60)/3)); //nombres de (3) minutes depuis le début de l'année (2 minutes : on est à 4 lettres à la fin de l'année ; 3 on reste à 3)
		if(in_array($webFolder,array('css','js','img','ie'))) $webFolder.='_';
		
		$jsFilenames=array('global.js','jsapp.js');
		foreach($entries as $entry){
			$jsFilenames[]=$entry.'.js';
			$options['exclude'][]='/'.$entry.'.php';
		}
		foreach($jsFilenames as $jsfilename){
			if(file_exists($filename=$projectPath.'web/js/'.$jsfilename)){
				$jsFile=file($filename);
				$resp->push('First line : '.$jsfilename."\n".$jsFile[0]);
				$line0="'use strict';var basedir='".$this->base_url."',staticUrl=basedir+'web/',webUrl=staticUrl+'".$webFolder."/',imgUrl=webUrl+'img/',version='".$webFolder."'";
				if($jsfilename==='admin.js') $line0.=',entryUrl='.json_encode($envConfig['siteUrl'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
				$line0.=";\n";
				if($jsFile[0]!=$line0){
					$jsFile[0]=substr($jsFile[0],0,12)==='var basedir='||substr($jsFile[0],0,12+13)==="'use strict';var basedir=" ? $line0 : $line0.$jsFile[0];
					file_put_contents($filename,implode('',$jsFile));
				}
			}
		}
		
		$options['exclude']=array('.svn/');
		if(is_dir($dbPath=$projectBasePath.'db/'))
			$resp->push('SYNC DB DIR'.PHP_EOL.UExec::rsync($dbPath,$target.'db/',$options));
		/*if(is_dir($soapPath=$projectBasePath.'data/soap/')){
			$options['exclude']=array('.svn/','*.cache');
			$resp->push('SYNC DB DIR'.PHP_EOL.UExec::rsync($soapPath,$target.'data/soap/',$options));
		}*/
		
		$options['exclude']=array('.svn/');
		
		//$resp->push('SYNC dbEvolutions dir'.PHP_EOL.UExec::rsync($projectPath.'dbEvolutions',$target.'dbEvolutions/',$options));
		
		
		if($stopProject) $resp->push($this->stop($scPath));
		
		$options['exclude']=array('logs/','web/files/*','db','data','.htaccess','authfile','/schema.php','/job.php','/cli.php','/index.php',/*'/dbEvolutions',*/'/currentDbVersion','/lastWebFolder','/web/'.$lastWebFolder);
		foreach($entries as $entry) $options['exclude'][]='/'.$entry.'.php';
		/*$res.=UExec::rsync(dirname(CORE).DS.'prod'.DS,$this->server->core_dir.DS.$sc->path.DS,$options);*/
		$resp->push('SYNC'.PHP_EOL.UExec::rsync($projectPath,$target,$options));
		
		$resp->push('EXECUTE schema.php'.PHP_EOL
			.($resSchema=UExec::exec('php '.escapeshellarg($target.'schema.php'),$options['ssh']+array('forcePseudoTty'=>true))));
		$shemaProcessSuccess=('Schema processed'===substr($resSchema,-strlen('Schema processed')));
		
		
		$resp->push('CREATE symb link: cd '.escapeshellarg($target.'web/').' && ln -s . "'.$webFolder.'"'.PHP_EOL
			.UExec::exec('cd '.escapeshellarg($target.'web/').' && ln -s .'.($webFolder[0]==='-'?' --':'').' "'.$webFolder.'"',$options['ssh']));
		
		$resp->push('Make sure the rights are good'.PHP_EOL
			.UExec::exec('cd '.escapeshellarg($target).' && chmod -R --quiet 775 web/ controllers* views* config/ helpers/ libs/ models/',$options['ssh']));
		
		//$resp->push('Delete CACHE files'.PHP_EOL
		//	.UExec::exec('cd '.escapeshellarg($target.'data/').' && rm -f cache/* ; rm -f cache/*/* ; rm -f elementsCache/* ; rm -f elementsCache/*/*',$options['ssh']));
		
		if($shemaProcessSuccess) $resp->push($this->start($scPath,$webFolder));
		
		UExec::exec('cd / && echo '.escapeshellarg($webFolder).' > '.escapeshellarg($target.'lastWebFolder'),$sshOptions);
		
		
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
		
		$this->stopDaemon($workspaceId);
		
		/* Delete tmp file */
		unlink($tmpfname);
	}
	
	
	private function baseDefine($scPath){
		return "
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".$this->server->core_dir.DS.$scPath.DS."');
define('CLIBS','".$this->server->core_dir."/libs/');
define('APP', __DIR__.DS);";
	}
	
	
	private $daemonStarted;
	private function startDaemon($workspaceId){
		if(true===($resDaemon=CDaemons::startIfNotAlive('Ssh',$workspaceId.'-'.$this->server_id))){
			$daemonStarted=true;
			sleep(2);
		}
		return 'Start daemon: '.($resDaemon===true?'succeeded':$resDaemon).PHP_EOL;
	}
	private function stopDaemon($workspaceId){
		return 'Kill daemon : '.(CDaemons::kill('Ssh',$workspaceId.'-'.$this->server_id)?'succeeded':'failed');
	}
	
	/* NEED : project,server */
	public function start($scPath=NULL,$appVersion){
		if($scPath===null){
			$scPath=$this->server->findLastVersion($this,$resp=new ABasicResp(),false);
			if(empty($scPath)) return $resp->getResp();
			$res=$resp->getResp();
		}
		
		$indexContentStarted="<?php".$this->baseDefine($scPath)."
define('APP_DATE',".time().");define('APP_VERSION','".$appVersion."'); define('WEB_FOLDER','".$appVersion."/');
include CORE.'app.php';";

		$tmpfname = tempnam('/tmp','projectstart');
		file_put_contents($tmpfname,$indexContentStarted);
		$entries=$this->project->entries();
		$sshOptions=$this->server->sshOptions();
		$target=$this->path().DS;

		$res='=> START PROJECT'.PHP_EOL
			.UExec::copyFile($tmpfname,$target.'index.php',$sshOptions);
		
		if(!empty($entries))
			foreach($entries as $entry)
				$res.=PHP_EOL.'=> START ENTRANCE: '.$entry.PHP_EOL
					.UExec::copyFile($tmpfname,$target.$entry.'.php',$sshOptions);
		
		if(file_exists($deamonsFilePath=$this->getProjectPath().'config/daemons.php')){
			$res.=PHP_EOL.'=> START daemons'.PHP_EOL;
			$res.= UExec::exec('php '.escapeshellarg($target.'cli.php').' daemons',$options['ssh']+array('forcePseudoTty'=>true));
		}
		
		unlink($tmpfname);
		return $res;
	}
	
	/* NEED : project,server */
	public function stop($scPath=null,$workspaceId=null){
		$res=''; $daemonStarted=false;
		if($scPath===null){
			if(empty($workspaceId)) return 'Deployment::stop: missing "workspaceId"';
			$res.=$this->startDaemon($workspaceId);
			
			$scPath=$this->server->findLastVersion($this,$resp=new ABasicResp(),false);
			if(empty($scPath)) return $resp->getResp();
			$res.=$resp->getResp();
		}
		
		
		$indexContentStopped="<?php
header('HTTP/1.1 503 Service Temporarily Unavailable',true,503);".$this->baseDefine($scPath)."
if(file_exists((".'$filename'."=CORE.'maintenance.php'))){
	define('APP_DATE',".time()."); define('APP_VERSION',''); define('WEB_FOLDER','');
	include ".'$filename'.";
}else echo '<h1>503 Service Temporarily Unavailable</h1>';";

		$tmpfname = tempnam('/tmp','projectstop');
		file_put_contents($tmpfname,$indexContentStopped);
		$entries=$this->project->entries();
		$sshOptions=$this->server->sshOptions();
		$target=$this->path().DS;
		
		$res.=PHP_EOL.'=> STOP PROJECT'.PHP_EOL
			.UExec::copyFile($tmpfname,$target.'index.php',$sshOptions);
		if(!empty($entries))
			foreach($entries as $entry)
				$res.=PHP_EOL.'=> STOP ENTRANCE: '.$entry.PHP_EOL
					.UExec::copyFile($tmpfname,$target.$entry.'.php',$sshOptions);
		
		unlink($tmpfname);
		
		if(file_exists($deamonsFilePath=$this->getProjectPath().'config/daemons.php')){
			//$res.=PHP_EOL.'=> KILL DAEMONS: '.$entry.PHP_EOL.UExec::exec('killall php',$sshOptions);
		}

		$this->stopDaemon($workspaceId);
		
		return $res;
	}
}