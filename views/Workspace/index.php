<?php $v=new AjaxContentView(_t('Workspaces'),'workspaces'); ?>
<?php $tableworkspaces->display() ?>
<div class="center italic"><? HHtml::link(_t('Add a workspace'),'/workspace/add') ?></div>