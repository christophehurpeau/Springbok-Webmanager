<?php new AjaxContentView(_t('Project:').' '.$deployment->project->name,'project'); ?>
{include _viewmenu.php}
<div class="content">
<div><?= _tF('Deployment','server_id') ?> : {link $deployment->server->name,'/servers/view/'.$deployment->server_id,array('class'=>'bold')}</div>
<div><?= _tF('Deployment','path') ?> : {$deployment->path()}</div>
<div><?= _tF('Deployment','base_url') ?> : {$deployment->base_url}</div>
</div>
<div class="content sepTop">
<i><?= _t('Actions:') ?></i>
<ul>
	<li><?php $form=HForm::create(null,array('action'=>'/projectDeployment/deploy/'.$deployment->id)); echo $form->hidden('post','1'); echo $form->end(_t('Deploy')); ?></li>
	<li><?php $form=HForm::create(null,array('action'=>'/projectDeployment/stop/'.$deployment->id)); echo $form->hidden('post','1'); echo $form->end(_t('Stop project')); ?></li>
	<li><?php $form=HForm::create(null,array('action'=>'/projectDeployment/start/'.$deployment->id)); echo $form->hidden('post','1'); echo $form->end(_t('Start project')); ?></li>
	<li>{link _t('Modify'),'/projectDeployment/edit/'.$deployment->id}</li>
</ul>
</div>