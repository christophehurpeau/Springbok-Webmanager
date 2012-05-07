<?php $v=new AjaxContentView(_t('Add project'),'project') ?>
<?php $form=HForm::create('Project');
	echo $form->fieldsetStart(_t('Add a new project'));
	//echo $form->input('name');
	//echo $form->input('path');
	$form->all();
	echo $form->end();
?>