<?php $v=new AjaxContentView(_t('Update a workspace'),'workspaces') ?>
<?php $form=HForm::create('Workspace');
	echo $form->fieldsetStart(_t('Update a workspace'));
	$form->all();
	$form->end();
?>