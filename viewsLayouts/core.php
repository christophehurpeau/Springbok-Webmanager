<?php $v=new AjaxPageView($layout_title); ?>

<div class="variable padding">
	<h1>{$layout_title}</h1>
	<? HMenu::top(array(
		_t('Infos')=>array('/core','startsWith'=>false),
		_t('Langs')=>'/core/langs'
	),array('startsWith'=>true)); ?>
	
	{=$layout_content}
</div>