<?php $v=new AjaxContentView(_t('Add plugin'),'plugin') ?>
<?php $form=HForm::create('Plugin');
	echo $form->fieldsetStart(_t('Add a new plugin'));
	$form->all();
	echo $form->end();
?>