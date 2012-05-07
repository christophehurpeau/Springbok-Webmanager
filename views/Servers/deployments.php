<?php new AjaxContentView(_t('Server:').' '.$server->name,'servers'); ?>
{include _viewmenu.php}
<div class="content">
<?php if(empty($server->deployments)): echo _t('No deployments.'); else: ?>
<div><h2><? $count=count($server->deployments)?> <?= _t_p('deployment',$count) ?></h2>
	<ul>
<?php foreach($server->deployments as $deployment): ?>
	<li>{link $deployment->project->name,'/project/view/'.$deployment->project_id}</li>
<?php endforeach; ?>
</ul></div>
<?php endif; ?>
</div>