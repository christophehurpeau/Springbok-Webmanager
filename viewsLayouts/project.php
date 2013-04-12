<?php $v=new AjaxPageDynamicTabsView($layout_title); ?>
<div class="fixed left w200">
	<?php $projects=Project::listAndOpen(); ?>
	<h2><? $count=count($projects) ?> <?= _t_p('project',$count) ?></h2>
	<nav class="left">
		<ul>
	<?php if(empty($projects)): ?>
		<li><?= _t('No projects') ?>.</li>
	<?php else: ?>
		<?php foreach($projects as $id=>$p): ?>
			<li><? HHtml::link($p['name'],'/project/view/'.$p['id']) ?>
				{if!e $p->git} [{$p->git->currentBranch()}]{/if}</li>
		<?php endforeach; ?>
	<?php endif;?>
		<li><i><? HHtml::link(_t('Add project'),'/project/add') ?></i></li>
		</ul>
	</nav>
</div>
<div class="variable padding"><h1>{$layout_title}</h1>{=$layout_content}</div>