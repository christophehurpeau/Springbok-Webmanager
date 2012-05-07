<?php $v=new AjaxPageView('PHP Doc') ?>

<div class="fixed left w200">
	<?php $form=HForm::create(NULL,array('style'=>'margin:5px 2px 0','class'=>'center')) ?>
	<? $form->input('search',array('label'=>false,'style'=>'width:80%')) ?>
	<? $form->end('Rechercher'); ?>
	{if!e $search_res}
	<h2>Suggestions:</h2>
	<ul>{f $search_res as $item}
		<?php $name=basename($item);
			$name2=str_replace(h($search),'<b style="color:orange">'.h($search).'</b>',h($name));
		?>
		<li><? HHtml::link($name2,'/phpdoc/'.$name,array('title'=>$name,'escape'=>false)) ?></li></a>
	{/f}</ul>
	{/if}
</div>

<div class="variable padding"{* style="height:100%;overflow:hidden"*}>
	<?php /*<iframe style="width:100%;height:100%;padding:0;margin:0;border:0" src="/springbok/webmanager/php-chunked-xhtml/">
	</iframe> */ ?>
	<? $content ?>
</div>{*
<?php HHtml::jsInlineStart() ?>
$('#page').css({
	height:$('#container').height()-$('header').height()-$('footer').height()-20
});
<? HHtml::jsInlineEnd() ?>
*}