<?php new AjaxContentView(_t('Project:').' '.$deployment->project->name,'project'); ?>
{include _viewmenu.php}
<div class="content">
<?php /* HHtml::tag('pre',array(),$output) */ ?>
<ul id="deployList"></ul>
</div>

<?php HHtml::jsInlineStart() ?>
S.ready(function(){
	var list=$('#deployList');
	{if $confirm}if(!confirm('Êtes-vous sûr de vouloir déployer ?')){
		$('<li class="content"/>').text('Déploiement annulé.').appendTo(list);
		return;
	}{/if}
	if(!window.EventSource){
		alert('Votre navigateur n\'est pas compatible avec EventSource');
		return;
	}
	var evtSource = new EventSource(basedir+"projectDeploymentServerSend/deploy/{=$deployment->id}");
	evtSource.onmessage = function(m){
		list.append($('<li class="content"/>').text(m.data));
	};
	evtSource.addEventListener('close',function(){
		evtSource.close();
	});
});
<? HHtml::jsInlineEnd() ?>