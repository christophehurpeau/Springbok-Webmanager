<?php $v=new AjaxContentView('Springbok CORE','core'); ?>


Current version : <? $current_version ?> du <? date('d/m/Y H:i:s',$current_version); ?><br />

<br />
<i><?= _t('Actions:') ?></i>
<ul>
	<li><? HHtml::link(_t('Enhance'),'../../core/enhance.php') ?></li>
</ul>
