<?php $v=new AjaxContentView(_t('Update project'),'project') ?>
<?php $form=HForm::create('Project');
	echo $form->fieldsetStart(_t('Update project'));
	//echo $form->input('name');
	//echo $form->input('path');
	$form->all();
	echo $form->end();
?>