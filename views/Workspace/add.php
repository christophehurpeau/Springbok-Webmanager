<?php $v=new AjaxContentView(_t('Add a workspace'),'workspaces') ?>
<?php $form=HForm::create('Workspace');
	echo $form->fieldsetStart(_t('Add a new workspace'));
	echo $form->input('name')
		.$form->input('projects_dir')
		.$form->input('db_name');
	$form->end();
?>