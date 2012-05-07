<?php new AjaxContentView(_t('Server:').' '.$server->name,'servers'); ?>
{include _viewmenu.php}
<div class="content">
	<h5>Actions</h5>
	<i><? HHtml::link(_t('Update core'),'/servers/core_update/'.$server->id) ?></i>
</div>
<div class="content sepatated">
	<h5>Liste des versions sur le serveur<?php $count=count($server->cores) ?>{if $count} ({=$count}){/if}</h5>
<?php if(empty($server->cores)): echo _t('No cores.'); else: ?>
	<ul>
<?php foreach($server->cores as $core): ?>
	<li><? HHtml::link($core->version.' ('.$core->path.') : '.$core->deployments.' '._t_p('deployment',$core->deployments),'/servers') ?>{if $core->deployments==0} <? HHtml::link('','/servers/core_delete/'.$core->id,array('class'=>'action delete')) ?>{/if}</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</div>