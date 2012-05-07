<?php $v=new AjaxContentView(_t('Update a server'),'servers') ?>
<?php $form=HForm::create('Server',array('method'=>'file'));
	echo $form->fieldsetStart(_t('Update a server'));
	$form->all();
?>
<div><label>Clé publique : </label><input type="file" name="public_key" /></div>
<div><label>Clé privée : </label><input type="file" name="private_key" /></div>
<? $form->end(); ?>