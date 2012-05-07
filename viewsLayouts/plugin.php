<?php $v=new AjaxPageView($layout_title); ?>
<div class="fixed left w200">
	<h2><? $count=count($pluginsPaths) ?> <?= _t_p('plugins paths',$count) ?></h2>
	<nav class="left">
		<ul>
		<?php if(empty($pluginsPaths)): ?>
			<li><?= _t('No plugins paths') ?>.</li>
		<?php else: ?>
			<?php foreach($pluginsPaths as $id=>$name): ?>
				<li><? HHtml::link($name,'/plugin/view_pluginsPath/'.$id) ?></li>
			<?php endforeach; ?>
		<?php endif;?>
			<li><i><? HHtml::link(_t('Add plugins path'),'/plugin/path_add') ?></i></li>
		</ul>
	</nav>
	
	<h2><? $count=count($plugins) ?> <?= _t_p('plugin',$count) ?></h2>
	<nav class="left">
		<ul>
		<?php if(empty($plugins)): ?>
			<li><?= _t('No plugins') ?>.</li>
		<?php else: ?>
			<?php foreach($plugins as $id=>$name): ?>
				<li><? HHtml::link($name,'/plugin/view/'.$id) ?></li>
			<?php endforeach; ?>
		<?php endif;?>
			<li><i><? HHtml::link(_t('Add plugin'),'/plugin/add') ?></i></li>
		</ul>
	</nav>
</div>
<div class="variable padding"><h1>{$layout_title}</h1>{=$layout_content}</div>