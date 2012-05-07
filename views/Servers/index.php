<?php $v=new AjaxContentView(_t('Servers'),'servers') ?>
<?php HTable::table($tableservers) ?>
<div class="center italic"><? HHtml::link(_t('Add a server'),'/servers/add') ?></div>
