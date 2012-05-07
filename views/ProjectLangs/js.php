<?php new AjaxContentView(_t('Project:').' '.$project->name.' -- '.$lang,'project'); ?>
{include ../Project/_viewmenu.php}
<div class="content">
<div><?php $form=HForm::create(NULL,array('action'=>'/projectLangs/js_save/'.$project->id.'/'.$lang));
	$i=0;
	foreach($allStrings as $filename=>$string){
		echo $form->hidden('data['.$i.'][s]',$string);
		echo $form->input('data['.$i.'][t]',array('label'=>$string,'id'=>'data_'.$i,'value'=>isset($translations[$string])?$translations[$string]:''));
		$i++;
	}
	$form->end(_t('Save'));
?></div>
</div>
