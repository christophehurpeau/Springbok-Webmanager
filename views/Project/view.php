<?php new AjaxContentView(_t('Project:').' '.$project->name,'project'); ?>
{include _viewmenu.php}
<div class="content">
<?php foreach($project as $key=>$value): ?>
	<div>
		<?= _tF('Project',$key) ?> : <?= $value ?>
	</div>
<?php endforeach; ?>
</div>
<div class="content sepTop">
	<i><?= _t('Actions:') ?></i>
	<ul>
		<li><? HHtml::link(_t('Enhance'),'/project/enhance/'.$project->id) ?></li>
		<li>{link 'Process schema','/project/schema/'.$project->id}</li>
		<li><? HHtml::link(_t('Start PROD'),'/project/start_prod/'.$project->id) ?></li>
		<li><? HHtml::link(_t('Recreate structure'),'/project/createStructure/'.$project->id,array('confirm'=>_t('Are you sure ?'))) ?></li>
	</ul>
</div>