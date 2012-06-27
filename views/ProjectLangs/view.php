<?php new AjaxContentView(_t('Project:').' '.$project->name,'project'); ?>
{include ../Project/_viewmenu.php}
<div class="content">
<?php if(empty($project->langs)): echo _t('No langs.'); else: ?>
<div><h2><? $count=count($project->langs)?> <?= _t_p('lang',$count) ?></h2>
	<ul>
<?php foreach($project->langs as $lang): ?>
	<li>{$lang->name} : <? HHtml::link('project','/projectLangs/lang/'.$project->id.'/'.$lang->name) ?>
		 - <? HHtml::link('project singular/plural','/projectLangs/sp/'.$project->id.'/'.$lang->name) ?>
		 - <? HHtml::link(_t('Models'),'/projectLangs/models/'.$project->id.'/'.$lang->name); ?>
		 - <? HHtml::link('js','/projectLangs/js/'.$project->id.'/'.$lang->name); ?>
		 - <? HHtml::link(_t('Plugins'),'/projectLangs/plugins/'.$project->id.'/'.$lang->name); ?>
		 &nbsp; {iconAction 'delete','/projectLangs/delete/'.$lang->project_id.'/'.$lang->name}</li>
<?php endforeach; ?>
</ul></div>
<?php endif; ?>
<div><?php $form=HForm::create('ProjectLang',array('action'=>'/projectLangs/add/'.$project->id));
	echo $form->input('name',array(),false);
	$form->end(_t('Add a new lang'));
?></div>
</div>
