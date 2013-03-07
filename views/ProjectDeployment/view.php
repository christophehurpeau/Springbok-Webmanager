<?php new AjaxContentView(_t('Project:').' '.$deployment->project->name." - ".$deployment->name(),'project'); ?>
{include _viewmenu.php}
<div class="content">
<div><?= _tF('Deployment','server_id') ?> : {link $deployment->server->name,'/servers/view/'.$deployment->server_id,array('class'=>'bold')}</div>
<div><?= _tF('Deployment','path') ?> : {$deployment->path()}</div>
<div><?= _tF('Deployment','base_url') ?> : {$deployment->base_url}</div>
</div>
<div class="content sepTop">
<i><?= _t('Actions:') ?></i>
<ul>
	<li><?php $form=HForm::Post()->attrClass('inline')->noContainer(); ?>
		<? $form->action('/projectDeployment/deploy/'.$deployment->id.'?').$form->hidden('post','1').$form->end(_t('Deploy')); ?> / 
		<? $form->action('/projectDeployment/deploy/'.$deployment->id.'?projectStop=1').$form->hidden('post','1').$form->end('Deploy and force to stop'); ?> / 
		<? $form->action('/projectDeployment/deploy/'.$deployment->id.'?projectStopBeforeDbEvolution=1').$form->hidden('post','1').$form->end('Deploy and stop before dbEvolution'); ?>
	</li>
	<li><? $form->action('/projectDeployment/stop/'.$deployment->id).$form->hidden('post','1').$form->end(_t('Stop project')); ?></li>
	<li><? $form->action('/projectDeployment/start/'.$deployment->id).$form->hidden('post','1').$form->end(_t('Start project')); ?></li>
	<li>{link _t('Modify'),'/projectDeployment/edit/'.$deployment->id}</li>
</ul>
</div>