<?php new AjaxContentView(_t('Plugin:').' '.$plugin->name,'plugin'); ?>
{include _viewmenu.php}
<div class="content">
<?php foreach($plugin as $key=>$value): ?>
	<div>
		<?= _tF('Plugin',$key) ?> : <?= $value ?>
	</div>
<?php endforeach; ?>
	<h5 class="sepTop">{t 'Actions:'}</h5>
	<ul>
		<li><? HHtml::link(_t('Enhance'),'/plugin/enhance/'.$plugin->id) ?></li>
		<li>{link 'Process schema','/plugin/schema/'.$plugin->id}</li>
	</ul>

	<h5 class="sepTop">{t 'Deployments'}</h5>
	{ife $deployments}
		<div class="italic">{t 'No deployments.'}</i>
	{else}
		<div><i><? $count=count($deployments) ?> <?= _t_p('deployment',$count) ?></i>
			<ul>
			{f $deployments as $deployment}
				<li><? HHtml::link($deployment->server->name,'/servers/view/'.$deployment->server_id,array('class'=>'bold')) ?> - {$deployment->path()} : <? HHtml::link('deploy with schema','/plugin/do_deployment/'.$deployment->id.'/1') ?> - <? HHtml::link('deploy without schema','/plugin/do_deployment/'.$deployment->id.'/0') ?>
					 &nbsp; <? HHtml::iconLink('delete',NULL,'/plugin/deployment_delete/'.$deployment->id) ?></li>
			{/f}
			</ul>
		</div>
	{/if}
	<?php $form=HForm::create('PluginDeployment',array('action'=>'/plugin/deployment_add'),false);
		echo $form->hidden('plugin_id',$plugin->id);
		echo $form->select('server_id',$servers,array());
		$form->end(_t('Add a new deployment')); ?>
	
	<h5 class="sepTop">{t 'Linked projects'}</h5>
	{ife $plugin->projects}
		<div class="italic">{t 'No projects'}</div>
	{else}
		<ul class="sortable">
			{f $plugin->projects as $project}<li id="prj_{=$project->id}" class="ui-state-default">{$project->name}</li>{/f}
		</ul>
		<?php HHtml::jsInlineStart() ?>
		$( ".sortable" ).sortable({
			placeholder: "ui-state-highlight",
			update: function(){
				$.post(baseUrl+'plugin/sortProjects/{=$plugin->id}',$(this).sortable("serialize"));
			}
		}).disableSelection();
		<? HHtml::jsInlineEnd() ?>
	{/if}
	<?php $form=HForm::create('PluginProject',array('action'=>'/plugin/project_add'),false);
		echo $form->hidden('plugin_id',$plugin->id);
		echo $form->select('project_id',$projects,array());
		$form->end(_t('Add a linked project')); ?>

</div>