<?php new AjaxContentView(_t('Project:').' '.$project->name,'project'); ?>
{include _viewmenu.php}
<div class="content">
<?php if(empty($deployments)): echo _t('No deployments.'); else: ?>
<div><h2><? $count=count($deployments)?> <?= _t_p('deployment',$count) ?></h2>
	<ul>
<?php foreach($deployments as $deployment): ?>
	<li>{link $deployment->server->name.' - '.$deployment->server->projects_dir.$deployment->path,'/project/deployment/'.$deployment->id}
		 {iconLink 'delete','Supprimer','/project/deployment_del/'.$deployment->id,array('confirm'=>_t('Are you sure ?'))}
		 {iconLink 'edit','Modifier','/project/deployment_edit/'.$deployment->id}
	</li>
<?php endforeach; ?>
</ul></div>
<?php endif; ?>
<div><?php $form=HForm::create('Deployment',array('action'=>'/project/deployment_add'));
	echo $form->hidden('project_id',$project->id);
	echo $form->select('server_id',$servers,array(),false);
	echo $form->input('path',array('label'=>false),false);
	$form->end(_t('Add a new deployment'));
?></div>
</div>
