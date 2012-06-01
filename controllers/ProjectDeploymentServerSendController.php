<?php
class ProjectDeploymentServerSendController extends SControllerServerSentEvents{
	
	public static function beforeDispatch(){
		AController::beforeDispatch();
	}
	
	/** @ValidParams @Required('id') */
	function deploy(int $id){
		$deployment=Deployment::ById($id)->with('Project')->with('Server');
		notFoundIfFalse($existingDeployment);
		
		$projectPath=dirname($deployment->getProjectPath()).'/';
		if(file_exists($projectPath.'block_enhance')){
			self::$resp->data('ERROR: An enhance is still in progress. Aborted.');
			self::$resp->push();
		}elseif(file_exists($projectPath.'block_delayedEnhanceDaemon')){
			self::$resp->data('ERROR: The delayed enhance daemon is still active. Aborted.');
			self::$resp->push();
		}else{
			file_put_contents($projectPath.'block_deploy','');
			$deployment->doDeployment(AController::$workspace->id,self::$resp);
			unlink($projectPath.'block_deploy');
		}
		
		
		self::$resp->event('close');
		self::$resp->data('');
		self::$resp->push();
		usleep(100);
		return true;
		
	}
	
}