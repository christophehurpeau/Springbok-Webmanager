<?php $v=new AjaxContentView(_t('Workspaces'),'workspaces'); ?>
<?php HTable::table($tableworkspaces) ?>
<div class="center italic"><? HHtml::link(_t('Add a workspace'),'/workspace/add') ?></div>