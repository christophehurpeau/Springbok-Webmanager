<?php new AjaxContentView(_t('Plugin:').' '.$plugin->name,'plugin'); ?>
{include _viewmenu.php}
<div class="content">
<h2>Changes</h2>
{f $changes as $type=>$files}
	<h5>{$type}</h5>
	<ul>
		{f $files as $file}
		<li>{$file}</li>
		{/f}
	</ul>
{/f}
</div>