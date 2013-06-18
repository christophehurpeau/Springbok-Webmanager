<?php new AjaxContentView(_t('Project:').' '.$project->name,'project'); ?>
{include ../Project/_viewmenu.php}
<div class="content">
<?php if(empty($groups)): echo _t('No permissions.'); else: ?>
<div>
	<form method="get" action="#" onsubmit="return false">
		{f $groups as $group=>$permissions}
		<fieldset class="clearfix">
			<legend>{$group}</legend>
			<ul{if $group!=='No group'} rel="{$group}"{/if} class="connectedSortable sortable mosaic" style="min-height:15px">
				{f $permissions as $permission}
					<li id="perms_{$permission}" class="ui-state-default" style="background:#000;margin:2px;padding:4px">{$permission}</li>
				{/f}
			</ul>
		</fieldset>
		<br/>
		{/f}
	</form>
	<?php HHtml::jsInlineStart() ?>
	$(document).ready(function(){
		$( ".sortable" ).sortable({
			placeholder: "ui-state-highlight",
			connectWith: ".connectedSortable",
			update: function(){
				var rel=$(this).attr("rel");
				if(rel) $.post(baseUrl+'projectAcls/sort/{=$project->id}/'+rel,$(this).sortable("serialize"));
			}
		}).disableSelection();
	});
	<? HHtml::jsInlineEnd() ?>
	
	<?php $form=HForm::create(null,array('action'=>'/projectAcls/addGroup','class'=>'sepTop'),false);
		echo $form->hidden('id',$project->id);
		echo $form->input('name',array('label'=>false));
		$form->end(_t('Add a group')); ?>
</div>
<?php endif; ?>