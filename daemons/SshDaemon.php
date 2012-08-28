<?php
class SshDaemon extends Daemon{
	public static function start($instance){
		CLogger::get('SshDaemon')->log('start: '.$instance);
		list($workspaceId,$serverId)=explode('-',$instance,2);
		
		$workspace=Workspace::findOneById($workspaceId);
		if(empty($workspace)) exit('not a valid workspace');
		SSqlModel::$__dbName=$workspace->db_name;
		SSqlModel::$__modelDb=DB::init(SSqlModel::$__dbName);
		
		$server=Server::findOneById($serverId);
		if($server===false) exit;
		$sshOptions=$server->sshOptions($workspace->name);
		//while(true){
			//$timeStart=microtime(true);
			shell_exec('killall ssh; killall ssh-agent');
			UExec::createPersistantSsh($sshOptions,2000);
			//if(microtime(true) - $timeStart < 5) break;
		//}
	}
}
