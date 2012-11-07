<?php new AjaxContentView(_t('Project:').' '.$deployment->project->name." - DEPLOY : ".$deployment->name(),'project'); ?>
{include _viewmenu.php}
<div class="content">
<?php /* HHtml::tag('pre',array(),$output) */ ?>
<ul id="deployList"></ul>
</div>

{jsReady}
var list=$('#deployList');
{if $confirm}
	S.dialogs.confirm('Déploiement : <? $deployment->name() ?>','Êtes-vous sûr de vouloir déployer ?','Lancer le déploiement',function(){
{/if}
		if(!window.EventSource){
			alert('Votre navigateur n\'est pas compatible avec EventSource');
			return;
		}
		var evtSource = new EventSource(basedir+"projectDeploymentServerSend/deploy/{=$deployment->id}{? isset($_REQUEST['projectStop']) && $_REQUEST['projectStop']=='1' => '?projectStop=1' : ''}");
		evtSource.onmessage = function(m){
			var content=$('<li class="content"/>');
			if(m.data.sbStartsWith('WARNING')) content.attr('style','color:orange');
			else if(m.data.sbStartsWith('ERROR')) content.attr('style','color:red');
			else content.text(m.data);
			list.append(content);
		};
		evtSource.addEventListener('close',function(){
			evtSource.close();
		});
{if $confirm}
	},function(){
		$('<li class="content"/>').text('Déploiement annulé.').appendTo(list);
		return;
	});
{/if}
{/jsReady}