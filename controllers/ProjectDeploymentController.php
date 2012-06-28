<?php
Controller::$defaultLayout='project';
class ProjectDeploymentController extends AController{
	/** @ValidParams @AllRequired */
	function all(int $id){
		$project=Project::ById($id);
		notFoundIfFalse($project);
		self::mset($project);
		self::set('deployments',Deployment::QAll()->byProject_id($id)->with('Server'));
		self::render();
	}
	
	/** @Post @ValidParams @AllRequired
	 * deployment > @Valid('project_id','server_id','path')
	 */
	function add(Deployment $deployment){
		$deployment->insert();
		self::redirect('/projectDeployment/all/'.$deployment->project_id);
	}
	
	/** @ValidParams @Required('id')
	 * deployment > @Valid('path','base_url')
	 */
	function edit(int $id,Deployment $deployment){
		$existingDeployment=Deployment::ById($id)->with('Project');
		notFoundIfFalse($existingDeployment);
		if($deployment!==null){
			$deployment->id=$id;
			$deployment->update('path','base_url');
			self::redirect('/projectDeployment/all/'.$existingDeployment->project_id);
		}
		self::set_('deployment',$existingDeployment);
		render();
	}
	
	/** @ValidParams @Required('id') */
	function del(int $id){
		$prjId=Deployment::findValueProject_idById($id);
		notFoundIfFalse($prjId);
		Deployment::deleteOneById($id);
		redirect('/projectDeployment/all/'.$prjId);
	}
	
	
	/** @ValidParams @Required('id') */
	function view(int $id){
		$deployment=Deployment::ById($id)->with('Project')->with('Server');
		notFoundIfFalse($deployment);
		mset($deployment);
		set('project',$deployment->project);
		render();
	}
	
	
	
	/** @ValidParams @Post @Required('id') */
	function start(int $id){
		$deployment=Deployment::ById($id)->with('Project')->with('Server');
		notFoundIfFalse($deployment);
		CSession::setFlash($deployment->start());
		redirect('/projectDeployment/view/'.$id);
	}
	/** @ValidParams @Post @Required('id') */
	function stop(int $id){
		$deployment=Deployment::ById($id)->with('Project')->with('Server');
		notFoundIfFalse($deployment);
		CSession::setFlash($deployment->stop());
		redirect('/projectDeployment/view/'.$id);
	}
	
	
	/** @ValidParams @Required('id') */
	function deploy(int $id){
		$deployment=Deployment::ById($id)->with('Project')->with('Server');
		notFoundIfFalse($deployment);
		mset($deployment);
		set('project',$deployment->project);
		set('confirm',!CHttpRequest::isPOST());
		render();
	}
	
}