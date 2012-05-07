<?php $v=new AjaxContentView(_t('Add a server'),'servers') ?>
<?php $form=HForm::create('Server');
	$_POST['server']['projects_dir']='/var/www/';
	echo $form->fieldsetStart(_t('Add a new server'));
	$form->all();
	$form->end();
?>