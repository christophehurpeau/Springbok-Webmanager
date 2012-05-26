<?php $v=new AjaxContentView(_t('Servers'),'servers') ?>
<?php $tableservers->display() ?>
<div class="center italic"><? HHtml::link(_t('Add a server'),'/servers/add') ?></div>
