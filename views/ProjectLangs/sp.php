<?php new AjaxContentView(_t('Project:').' '.$project->name.' -- '.$lang,'project'); ?>
{include ../Project/_viewmenu.php}
<div class="content">
<div><?php $form=HForm::create(NULL,array('action'=>'/project/lang_sp_save/'.$project->id.'/'.$lang));
	$i=0;
	foreach($allStrings as $filename=>$string){
		echo $form->hidden('data['.$i.'][s]',$string);
		
		echo '<div class="input">';
		echo HHtml::tag('label',array('for'=>'data_s'.$i),$string);
		echo $form->input('data['.$i.'][singular]',array('label'=>false,'id'=>'data_s'.$i,'value'=>isset($translations[$string]['singular'])?$translations[$string]['singular']:''),false);
		echo $form->input('data['.$i.'][plural]',array('label'=>false,'id'=>'data_p'.$i,'value'=>isset($translations[$string]['plural'])?$translations[$string]['plural']:''),false);
		echo '</div>';
		$i++;
	}
	$form->end(_t('Save'));
?></div>
</div>
