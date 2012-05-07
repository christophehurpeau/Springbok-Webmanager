<?php new AjaxContentView(_t('Project:').' '.$project->name.' -- '.$lang,'project'); ?>
{include ../Project/_viewmenu.php}
<div class="content">
	<ul class="compact">
	{f $allStrings as $modelName=>$modelStrings}
		<li>{link $modelName,'#',array('onclick'=>'$(this).parent().find("> div").slideToggle();return false;')}
		<div class="hidden">
			<?php $form=HForm::create(NULL,array('id'=>'FormLangsFields_'.$modelName,'action'=>'#')); $i=0; ?>
			<ul>
			{f $modelStrings as $filename=>$string}
				<li>
					{=$form->input('data['.$string.']',array('label'=>empty($string)?'Table name':$string,'id'=>'data_'.$i++,'value'=>isset($translations[$modelName.':'.$string])?$translations[$modelName.':'.$string]:''))}
				</li>
			{/f}
			</ul>
			{=$form->end()}
		</div>
	{/f}
	</ul>
</div>
<?php HHtml::jsInlineStart() ?>
S.ready(function(){
{f $allStrings as $modelName=>$modelStrings}
	$("#FormLangsFields_{$modelName}").ajaxForm(basedir+"projectLangs/fields_save/{=$project->id}/{$lang}/{$modelName}");
{/f}
});
<? HHtml::jsInlineEnd() ?>