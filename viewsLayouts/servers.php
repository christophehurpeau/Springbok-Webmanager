<?php $v=new AjaxPageView($layout_title); ?>
<div class="fixed left w200">
	<h2><? $count=count($servers) ?> <?= _t_p('server',$count) ?></h2>
	<nav class="left">
		<ul>
		<?php if(empty($servers)): ?>
			<li><?= _t('No servers') ?>.</li>
		<?php else: ?>
			<?php foreach($servers as $id=>$name): ?>
				<li><? HHtml::link($name,'/servers/view/'.$id) ?></li>
			<?php endforeach; ?>
		<?php endif;?>
			<li><i><? HHtml::link(_t('Add server'),'/servers/add') ?></i></li>
			<li><i><? HHtml::link(_t('Init ssh'),'/servers/initSsh') ?></i></li>
		</ul>
	</nav>
</div>
<div class="variable padding"><h1>{$layout_title}</h1>{=$layout_content}</div>
