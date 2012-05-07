<?php $v=new AjaxContentView('Springbok CORE - '.$lang,'core'); ?>

<div><?php $form=HForm::create(NULL,array('action'=>'/core/lang_save/'.$lang));
	$i=0;
	foreach($allStrings as $filename=>$string){
		echo $form->input('data['.$string.']]',array('label'=>$string,'id'=>'data_'.$i,'value'=>isset($translations[$string])?$translations[$string]:''));
		$i++;
	}
	$form->end();
?></div>