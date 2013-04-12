<?php new AjaxContentView(_t('Project:').' '.$project->name,'project'); ?>
{include _viewmenu.php}
<div class="content">
<?php foreach($project as $key=>$value): if($key==='git') continue; ?>
	<div>
		<?= _tF('Project',$key) ?> : <?= $value ?>
	</div>
<?php endforeach; ?>
</div>
{if isset($project->git)}
<div class="content mtb10">
	<div class="row">
		<div class="col wp60">
			<h4>Git Status</h4>
			<pre><?= $project->git->run('status'); ?></pre>
		</div>
		<div class="col wp40">
			{if !$project->git->isUpToDate()}
				<b>Not up-to-date !</b>
			{/if}
		</div>
	</div>
</div>
{/if}
<div class="content sepTop">
	<i><?= _t('Actions:') ?></i>
	<ul>
		<li><? HHtml::link(_t('Enhance'),'/project/enhance/'.$project->id) ?></li>
		<li>{link 'Process schema','/project/schema/'.$project->id}</li>
		<li><? HHtml::link(_t('Start PROD'),'/project/start_prod/'.$project->id) ?></li>
		<li><? HHtml::link(_t('Recreate structure'),'/project/createStructure/'.$project->id,array('confirm'=>_tC('Are you sure ?'))) ?></li>
	</ul>
</div>