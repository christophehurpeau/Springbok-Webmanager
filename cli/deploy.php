<?php
$idProject=$argv[0];
$idDeployment=$argv[1];
$distantFolder=$argv[2];
$folder=$argv[3];

$deployment=Deployment::QOne()->with('Project')->with('Server')
	->where(array('d.id'=>$idDeployment,'p.id'=>$idProject));
if(empty($deployment)){
	echo 'Deployment or project unknown.'.PHP_EOL;
	exit;
}

$options=array('simulation'=>false,'ssh'=>$deployment->server->sshOptions(),
		'exclude'=>array());
$target=$deployment->path().DS;
UExec::rsync($folder,$target.$distantFolder,$options);