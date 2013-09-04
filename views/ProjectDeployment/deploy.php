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
		// capture F5
		var fnCaptureF5;
		$(document).on('keypress',fnCaptureF5=function(e){
			if(e.keyCode==116) return false;
		});
		
		var evtSource = new EventSource(baseUrl+"projectDeploymentServerSend/deploy/{=$deployment->id}{if isset($_REQUEST['projectStop']) && $_REQUEST['projectStop']=='1'}?projectStop=1{elseif isset($_REQUEST['projectStopBeforeDbEvolution']) && $_REQUEST['projectStopBeforeDbEvolution']=='1'}?projectStopBeforeDbEvolution=1{/if}");
		evtSource.onmessage = function(m){
			var content=$('<li class="content"/>');
			if(m.data.startsWith('WARNING')) content.attr('style','color:orange');
			else if(m.data.startsWith('ERROR')) content.attr('style','color:red');
			content.text(m.data);
			list.append(content);
		};
		evtSource.addEventListener('close',function(){
			evtSource.close();
			$(document).off('keypress',fnCaptureF5);
		});
{if $confirm}
	},function(){
		$('<li class="content"/>').text('Déploiement annulé.').appendTo(list);
		return;
	});
{/if}
{/jsReady}