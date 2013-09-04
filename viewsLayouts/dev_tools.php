<?php $v=new AjaxPageView($layout_title); ?>
<div class="fixed left w200">
	<h2>Dev Tools</h2>
	<? HMenu::left(array(
		_t('Tests')=>'/tests',
		_t('Colors')=>'/devtools/colors',
		'Pt / Px / Em / %'=>'/devtools/pt_px',
		'JQuery UI'=>'/devtools/jqueryui',
		'Css'=>'/devtools/css',
		'String compare'=>'/devtools/stringCompare',
	)) ?>
</div>
<div class="variable padding"><h1>{$layout_title}</h1>{=$layout_content}</div>