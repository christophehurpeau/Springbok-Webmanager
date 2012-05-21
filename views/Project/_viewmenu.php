<? HMenu::top(array(
	_t('Infos')=>'/project/view/'.$project->id,
	_t('Start IDE')=>array('url'=>'/editor/project/'.$project->id,'target'=>'_blank'),
	_t('Langs')=>'/projectLangs/view/'.$project->id,
	_t('Jobs')=>'/project/jobs/'.$project->id,
	_t('Deployments')=>'/projectDeployment/all/'.$project->id,
	_t('Acls')=>'/projectAcls/view/'.$project->id,
),array('startsWith'=>true)); ?>
