<?php $v=new AjaxContentView('Springbok CORE - langs','core'); ?>

<?php if(empty($langs)): echo _t('No langs.'); else: ?>
<div><ul>
<?php foreach($langs as $lang): ?>
	<li><? HHtml::link($lang,'/core/lang/'.$lang) ?></li>
<?php endforeach; ?>
</ul></div>
<?php endif; ?>
