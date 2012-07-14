<?php
Controller::$defaultLayout='project';
class ProjectTestsController extends AController{
	/** @ValidParams @Id */
	function index(int $id){
		$project=Project::ById($id);
		notFoundIfFalse($project);
		$tests=file_exists($filename=$project->path().'/tests.json') ? json_decode(file_get_contents($filename),true) : array();
		mset($project,$tests);
		render();
	}
	
	/** @ValidParams @Id */
	function save(int $id,array $tests){
		$project=Project::ById($id);
		notFoundIfFalse($project);
		file_put_contents($project->path().'/tests.json',json_encode($tests));
	}
	
}