<?php $v=new AjaxPageView($layout_title); ?>
<nav class="dynamictabs top"><ul></ul></nav>
<div id="dynamictabsContent" class="clear">
	<div class="fixed left w200">
		<?php $projects=Project::findListName(); ?>
		<h2><? $count=count($projects) ?> <?= _t_p('project',$count) ?></h2>
		<nav class="left">
			<ul>
		<?php if(empty($projects)): ?>
			<li><?= _t('No projects') ?>.</li>
		<?php else: ?>
			<?php foreach($projects as $id=>$name): ?>
				<li><? HHtml::link($name,'/project/view/'.$id) ?></li>
			<?php endforeach; ?>
		<?php endif;?>
			<li><i><? HHtml::link(_t('Add project'),'/project/add') ?></i></li>
			</ul>
		</nav>
	</div>
	<div class="variable padding"><h1>{$layout_title}</h1>{=$layout_content}</div>
</div>