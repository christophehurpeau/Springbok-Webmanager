<?php
Controller::$defaultLayout='project';
class ProjectController extends AController{
	
	/** */
	function index(){
		$tableprojects=CTable::create(Project::QAll());
		$tableprojects->setActionsRUD();
		self::mset($tableprojects);
		self::renderTable(_t('Projects'),$tableprojects);
	}
	
	/** @ValidParams
	* id > @Required
	*/ function view(int $id){
		$project=Project::ById($id);
		notFoundIfFalse($project);
		mset($project);
		render();
	}
	
	/** @ValidParams
	* id > @Required
	*/ function enhance(int $id){
		$project=Project::ById($id);
		notFoundIfFalse($project);
		$project->checkCli();
        $res=UExec::exec('php '.escapeshellarg($project->path().'/cli.php').' enhance');
        if(!empty($res)) debugCode($res);
		else self::redirect('/project/view/'.$id);
        exit;
        
		/* PROD */include dirname(CORE).'/dev/enhancers/EnhanceApp.php';/* /PROD */
		$f=new Folder($projectPath.DS.'tmp_dev'); if($f->exists()) $f->delete();
		$instance=new EnhanceApp($projectPath);
		$res=$instance->process(true);
		self::mset($project);
		self::set_('changes',$res);
		self::render();
	}
	
	/** @ValidParams
	* id > @Required
	*/ function schema(int $id){
		$project=Project::ById($id);
		if(empty($project)) notFound();
		$projectPath=self::$workspace->projects_dir.$project->path.'/';
		
		$baseDefine="<?php
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".dirname(CORE).DS."dev".DS."');
define('APP', __DIR__.'/dev/');";

		$schemaContent=$baseDefine."
".'$action'."='schema';
include CORE.'cli.php';";
		
		file_put_contents($projectPath.'schema.php',$schemaContent);
		CSession::setFlash(UExec::exec('php '.escapeshellarg($projectPath.'schema.php')));
		redirect('/project/view/'.$id);
	}
	

	/** @ValidParams
	* id > @Required
	*/ function start_prod(int $id){
		$project=Project::ById($id);
		notFoundIfFalse($project);
		$projectPath=self::$workspace->projects_dir.$project->path.'/prod/'.DS;
		
		$baseDefine="<?php
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".dirname(CORE)."/prod/');
define('APP', __DIR__.DS);";

		$indexProdContent=$baseDefine."
define('APP_DATE',".time()."); define('WEB_FOLDER','');
include CORE.'app.php';";

		$schemaContent=$baseDefine."
".'$action'."='schema';
include CORE.'cli.php';";
		file_put_contents($projectPath.'schema.php',$schemaContent);
		CSession::setFlash(UExec::exec('php '.escapeshellarg($projectPath.'schema.php')));
		file_put_contents($projectPath.'index.php',$indexProdContent);
		
		
		self::redirect('/project/view/'.$id);
	}
	
	
	
	
	/**
	* project > @Valid('name','path')
	*/ function add(Project $project){
		if($project!==NULL && !CValidation::hasErrors()){
			$projectPath=self::$workspace->projects_dir.$project->path.DS;
			mkdir($projectPath,0777,true);
			mkdir($projectPath.'tmp/',0755);
			mkdir($projectPath.'dev/',0755);
			mkdir($projectPath.'prod/',0755);
			mkdir($projectPath.'db/',0755);
			mkdir($projectPath.'data/',0775);
			mkdir($projectPath.'data/tmp/',0775);
			mkdir($projectPath.'sql/',0777);
			self::_createStructure($projectPath.'src/',$project->name);
			$project->insert();
			self::redirect('/project');
		}
		self::render();
	}
	
	/**
	* id > @Required
	*/ function edit(int $id){
		CRUD::edit('Project',$id);/*
		if(empty($id)) notFound();
		if($project!==NULL && !CValidation::hasErrors()){
			$project->id=$id;
			$project->update();
			self::redirect('/project/view/'.$id);
		}elseif(!$project) $project=Project::findOneById($id);
		self::mset($project);
		self::render();*/
	}
	
	
	private static function _createStructure($projectPath,$projectName){
		if(!file_exists($projectPath)){
			mkdir($projectPath,0777,true);
			mkdir($dir=$projectPath.'config/');
			file_put_contents($dir.'_.php',"<?"."php return array(\n\t'project_name'=>'".UInflector::underscore(preg_replace('/\s+/','',$projectName))."',\n\t'projectName'=>'".$projectName."',\n\t'default_lang'=>'fr',\n);");
			file_put_contents($dir.'_'.ENV.'.php',"<?"."php return array(\n\t'db'=>array(\n\t\t'_lang'=>dirname(dirname(__DIR__)).'/db/',\n\t\t'default'=>array(\n\t\t\t\n\t\t\t'user'=>'root','password'=>'root'\n\t\t),\n\t),'generate'=>array('default'=>true)\n\t\n);");
			file_put_contents($dir.'routes.php',"<?"."php return array(\n\t'/favicon'=>array('Site::favicon','ext'=>'[a-z]+'),\n\t'/robots'=>array('Site::robots','ext'=>'txt'),\n\t'/'=>array('Site::index'),"
				."\n\t'/:controller(/:action/*)?'=>array('!::!'),\n);");
			file_put_contents($dir.'routes-langs.php',"<?"."php return NULL;");
			file_put_contents($dir.'cookies.php',"<?"."php return array();");
			file_put_contents($dir.'secure.php',"<?"."php return array(\n\t'url_login'=>'/site/login', 'url_redirect'=>'/',\n\t\n);");
			file_put_contents($dir.'enhance.php',"<?"."php return array(\n\t'base'=>array('i18n'),\n\t'includes'=>array(\n\t\t\n\t)\n);");
			
			mkdir($dir=$projectPath.'controllers/');
			file_put_contents($dir.'SiteCont'.'roller.php',"<?"."php\ncl"."ass SiteContr"."oller extends Contr"."oller{\n"
				."\t/**\n*/\tfunct"."ion index(){\n\t\t".'self::render()'.";\n\t}\n"
				."\n\t/**\n\t*/ funct"."ion favicon(){\n\t\tself::renderFile(APP.'web/img/favicon.ico');\n\t}\n"
				."\n\t/**\n\t*/ funct"."ion robots(){\n\t\tself::renderText(".'"User-agent: *\nAllow: /\n"'.");\n\t}\n}");
			mkdir($dir=$projectPath.'models/');
			mkdir($dir=$projectPath.'views/');
			mkdir($dir2=$dir.'Site/');
			file_put_contents($dir2.'index.php',"<?"."php new AjaxContentView() ?".">");
			mkdir($dir=$projectPath.'viewLayouts/');
			file_put_contents($dir.'base.php',"<!DOCTYPE html>\n<html>\n\t<head>\n\t\t<meta charset=\"UTF-8\">\n\t\t<title>".'{$layout_title}'."</title>"
				."\n\t\t<?"."php HHtml::cssLink() ?".">\n\t</head>\n\t<body>"
				."\n\t\t".'{=$layout_content}'
				."\n\t</body>"."\n</html>");
			
			file_put_contents($dir.'page.php',"<?php new AjaxBaseView(".'$layout_title'.") ?>"
				."\n<header>\n\t<div id=\"logo\">".$projectName."</div>\n\t{menuTop 'startsWith':true"
				."\n\t\t_tC('Home'):false,\n"."\t\t}"
				."\n\t\t<br class=\"clear\"/>"
				."\n</header>\n".'{=$layout_content}'
				."\n<footer>Version du <b><? HHtml::enhanceDate() ?></b> | <? HHtml::powered() ?></footer>");
			file_put_contents($dir.'default.php',"<?php new AjaxPageView(".'$layout_title'.") ?>"
				."\n<div class=\"variable\">"
				."\n\t<h1>".'{$layout_title}'."</h1>"
				."\n\t".'{=$layout_content}'
				."\n</div>");
			mkdir($dir=$projectPath.'web/');
			mkdir($dir2=$dir.'css/');
			file_put_contents($dir2.'main.css',"@includeCore 'springbok-dawn.css';");
			mkdir($dir.'img/');
			mkdir($dir.'js/');
		}
	}

	/** @ValidParams
	* id > @Required
	*/ function createStructure(int $id){
		$project=Project::ById($id);
		notFoundIfFalse($project);
		$projectPath=self::$workspace->projects_dir.$project->path.DS.'src'.DS;
		self::_createStructure($projectPath,$project->name);
		self::redirect('/project/view/'.$id);
	}
	
	
	
	
	
	
	
	
	/** @ValidParams
	 * id > @Required
	 */
	function jobs(int $id){
		$project=Project::ById($id);
		notFoundIfFalse($project);
		self::mset($project);
		self::set('jobs',file_exists($filename=$project->path().DS.'dev'.DS.'config'.DS.'jobs.php') ? include $filename : false);
		self::render();
	}
	
	/** @ValidParams
	 * id > @Required
	 * name > @Required
	 */
	function job_execute(int $id,$name){
		$project=Project::ById($id);
		notFoundIfFalse($project);
		$jobs=include $project->path().DS.'dev'.DS.'config'.DS.'jobs.php';
		if(!isset($jobs[$name])) notFound();
		set_time_limit(0);
		self::set('output',UExec::php(CORE.'cron.php',$project->path().DS.'dev'.DS,$name));
		self::mset($project,$name);
		self::render();
	}
	
	/** @ValidParams
	 * id > @Required
	 */
	function deployments(int $id){
		$project=Project::ById($id);
		notFoundIfFalse($project);
		self::mset($project);
		self::set('deployments',Deployment::QAll()->byProject_id($id)->with('Server'));
		self::set('servers',Server::findListName());
		self::render();
	}
	
	/** @ValidParams
	 * deployment > @Valid('project_id','server_id','path')
	 * deployment > @Required
	 */
	function deployment_add(Deployment $deployment){
		$deployment->insert();
		self::redirect('/project/deployments/'.$deployment->project_id);
	}
	
	/** @ValidParams
	 * id > @Required
	 * deployment > @Valid('path','base_url')
	 */
	function deployment_edit(int $id,Deployment $deployment){
		$existingDeployment=Deployment::ById($id)->with('Project');
		notFoundIfFalse($existingDeployment);
		if($deployment!==NULL){
			$deployment->id=$id;
			$deployment->update('path','base_url');
			self::redirect('/project/deployments/'.$existingDeployment->project_id);
		}
		self::set_('deployment',$existingDeployment);
		render();
	}
	
	/** @ValidParams
	* id > @Required
	*/ function deployment_del(int $id){
		$prjId=Deployment::findValueProject_idById($id);
		notFoundIfFalse($prjId);
		Deployment::deleteOneById($id);
		redirect('/project/deployments/'.$prjId);
	}
	
	/** @ValidParams
	* id > @Required
	*/ function deployment(int $id){
		$deployment=Deployment::ById($id)->with('Project')->with('Server');
		notFoundIfFalse($deployment);
		self::mset($deployment);self::set_('project',$deployment->project);
		self::render();
	}
	
	/** @ValidParams @Post
	* id > @Required
	*/ function start_deployment(int $id){
		$deployment=Deployment::ById($id)->with('Project')->with('Server');
		notFoundIfFalse($deployment);
		CSession::setFlash($deployment->start());
		redirect('/project/deployment/'.$id);
	}
	/** @ValidParams @Post
	* id > @Required
	*/ function stop_deployment(int $id){
		$deployment=Deployment::ById($id)->with('Project')->with('Server');
		notFoundIfFalse($deployment);
		CSession::setFlash($deployment->stop());
		redirect('/project/deployment/'.$id);
	}
	
	/** @ValidParams @Post
	* id > @Required
	*/ function do_deployment(int $id){
		$deployment=Deployment::ById($id)->with('Project')->with('Server');
		notFoundIfFalse($existingDeployment);
		self::mset($deployment);self::set_('project',$deployment->project);
		self::set('output',$deployment->doDeployment(self::$workspace->id));
		self::render();
	}
	
	/*
	public function create_deployment(int $id){
		$deployment=Deployment::QOne()->byId($id)->with('Project')->with('Server');
		if(empty($deployment)) notFound();
		
		file_put_contents($deployment->project->path().DS.'depl_'.$deployment->server->name.'.php',"<?php
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".CORE."');
define('APP','".APP."');
define('ENV','dev');

".'$action'."='deployment';
".'$entrances'."=include __DIR__.DS.'src'.DS.'config'.DS.'enhance.php';
".'$entrances=$entrances["entrances"];'."
".'$argv'."=array(
	'type'=>'app','workspace_id'=>".self::$workspace->id.",'deployment_id'=>".$id.",
	'projectPath'=>__DIR__.DS.'prod'.DS,
	'entrances'=>".'$entrances'.",
	'dbPath'=>__DIR__.DS.'db'.DS,
	'options'=>array(
		'simulation'=>false,
		'ssh'=>".($deployment->ssh?"array('user'=>'".$deployment->server->user."','host'=>'".$deployment->server->host."')":'false').",
	)
);
include CORE.'cli.php';
");
		self::redirect('/project/deployment/'.$id);
	}*/
}