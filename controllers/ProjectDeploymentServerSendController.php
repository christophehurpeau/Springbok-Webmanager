<?php
class ProjectDeploymentServerSendController extends SControllerServerSentEvents{
	
	public static function beforeDispatch(){
		AController::beforeDispatch();
	}
	
	/** @ValidParams @Required('id') */
	function deploy(int $id){
		$deployment=Deployment::ById($id)->with('Project')->with('Server');
		notFoundIfFalse($existingDeployment);
		$deployment->doDeployment(AController::$workspace->id,self::$resp);
		
		self::$resp->event('close');
		self::$resp->data('');
		self::$resp->push();
		usleep(100);
		return true;
		
	}
	
}