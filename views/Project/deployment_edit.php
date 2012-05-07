<?php new AjaxContentView(_t('Project:').' '.$deployment->project->name,'project'); ?>
<?php $project=$deployment->project; ?>
{include _viewmenu.php}
<div class="content">
<?php $form=HForm::create('Deployment');
echo $form->input('path');
echo $form->input('base_url');
echo $form->end();
?>
</div>