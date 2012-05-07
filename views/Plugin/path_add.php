<?php $v=new AjaxContentView(_t('Add plugins path'),'plugin') ?>
<?php $form=HForm::create('PluginPath');
	echo $form->fieldsetStart(_t('Add a new plugins path'));
	$form->all();
	echo $form->end();
?>