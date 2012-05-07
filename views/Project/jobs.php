<?php new AjaxContentView(_t('Project:').' '.$project->name.' -- '._t('jobs'),'project'); ?>
{include _viewmenu.php}
<div class="content">
<?php if(empty($jobs)): echo _t('No jobs.'); else: ?>
<div><h2><? $count=count($jobs)?> <?= _t_p('job',$count) ?></h2>
	<ul>
<?php foreach($jobs as $jobClass=>$jobInfos): ?>
	<li>{$jobClass} : <? HHtml::link(_t('Execute'),'/project/job_execute/'.$project->id.'/'.$jobClass) ?></li>
<?php endforeach; ?>
</ul></div>
<?php endif; ?>
</div>
