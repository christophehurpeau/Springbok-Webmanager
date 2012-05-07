<?php new AjaxContentView(_t('Plugin path:').' '.$pluginPath->name,'plugin'); ?>
<div class="content">
<? $pluginPath->toHtml() ?>
</div>

<div class="content sepTop">
	{ife $deployments}<i>{t 'No deployments.'}</i>{else}
	<div><h2><? $count=count($deployments) ?> <?= _t_p('deployment',$count) ?></h2>
		<ul>
		{f $deployments as $deployment}
			<li><? HHtml::link($servers[$deployment->server_id],'/servers/view/'.$deployment->server_id) ?> - {$deployment->path()} :
				 <? HHtml::link('deploy','/plugin/path_do_deployment/'.$deployment->id) ?>
				 &nbsp; <? HHtml::iconLink('delete',NULL,'/plugin/path_deployment_delete/'.$deployment->id) ?></li>
		{/f}
		</ul>
	</div>
	{/if}
	<div>
		<h5 class="sepTop">{t 'Add a new deployment'}</h5>
		<?php $form=HForm::create('PluginPathDeployment',array('action'=>'/plugin/path_deployment_add'));
		echo $form->hidden('plugin_path_id',$pluginPath->id);
		echo $form->select('server_id',$servers,array());
		echo $form->input('folder_name');
		$form->end(_t('Add a new deployment'));
	?></div>
</div>
