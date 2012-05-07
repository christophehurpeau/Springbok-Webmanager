<?php new AjaxContentView(_t('Plugin:').' '.$plugin->name.' -- '._t('deployment'),'plugin'); ?>
{include _viewmenu.php}
<div class="content">
{ife $deployments}{t 'No deployments.'}{else}
<div><h2><? $count=count($deployments)?> <?= _t_p('deployment',$count) ?></h2>
	<ul>
{f $deployments as $deployment}
	<li><? HHtml::link($deployment->server->name,'/servers/view/'.$deployment->server_id) ?> - {$deployment->path_deployment}{$deployment->path} : <? HHtml::link('view','/project/deployment/'.$deployment->id) ?>
		 &nbsp; <? HHtml::iconLink('delete',NULL,'/plugin/deployment_delete/'.$deployment->id) ?></li>
{/f}
</ul></div>
{/if}
<div><?php $form=HForm::create('PluginDeployment',array('action'=>'/project/deployment_add'));
	echo $form->hidden('plugin_id',$plugin->id);
	echo $form->select('server_id',$servers,array(),false);
	$form->end(_t('Add a new deployment'));
?></div>
</div>
