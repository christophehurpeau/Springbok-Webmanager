<?php new AjaxContentView(_t('Project:').' '.$project->name,'project'); $i=0; ?>
{include ../Project/_viewmenu.php}

<div class="content">
	<div class="floatR">
		{link 'Lancer les tests','#',array('onclick'=>'return startTests(this)','class'=>'button')}
	</div>
	<?php $onChange="S.redirect('?id=".$project->id."&entry='+$('#SelectEntry').val()+'&env='+$('#SelectEnv').val())" ?>
	{if!e $entries}<? HHtml::select(array_combine($entries,$entries),array('id'=>'SelectEntry','onchange'=>$onChange,'selected'=>$entry)) ?>{/if}
	{if!e $environments}<? HHtml::select(array_combine($environments,$environments),array('id'=>'SelectEnv','onchange'=>$onChange,'selected'=>$env)) ?>{/if}
	<form id="FormTests" action="#" onsubmit="return false">
		{table class:'table'}
			<tr class="head"><th>Url</th><th></th><th style="width:130px">Type de test</th><th>Informations sur le test</th><th>Résultat du test</th><th>Détails sur le résulat</th></tr>
		{f $tests as $i=>$test}
			<tr id="tr{=$i}"<?php if($itable++%2) echo ' class="alternate"' ?>>
				<td class="w1">{cutLink 67,$test['url'],$envBaseUrl.ltrim($test['url'],'/'),array('onclick'=>"$(this).parent().children('div').toggle()",'target'=>'_blank')}
					<input name="tests[{=$i}][url]" type="hidden" value="{$test['url']}"/></td>
				<td class="w1">{iconAction 'delete','#',array('onclick'=>"var t=$(this);t.closest('tr').remove(); t.closest('form').submit()")}</td>
				<td><select name="tests[{=$i}][type]" onchange="updateType({=$i},this)"><? HHtml::_option(200,'200 OK',$test['type']).HHtml::_option(404,'404 Not Found',$test['type']).HHtml::_option(301,'301 Redirection',$test['type']) ?></select></td>
				<td>
					Contient :<input name="tests[{=$i}][content]" value="{=?e $test['content'] : ''}"/>
				</td>
				<td class="resultTest"></td>
				<td class="resultTest"></td>
			</tr>
		{/f}
		{/table}
	</form>
	
	<div class="clearfix sepTop">
		<div class="floatR">
			{link 'Lancer les tests','#',array('onclick'=>'return startTests(this)','class'=>'button')}
		</div>
		
		<?php $form=HForm::create(null,array('id'=>'FormAddTest','action'=>'/projectTests/addTest?id='.$project->id),false);
			echo $form->input('url',array('label'=>false,'class'=>'w600'));
			echo $form->end(_t('Add a test')); ?>
	</div>
</div>

	
<?php HHtml::jsInlineStart() ?>
	
	function startTests(a){
		a=$(a).hide();
		if(!window.EventSource){
			alert('Votre navigateur n\'est pas compatible avec EventSource');
			return;
		}
		$('#FormTests .resultTest').html('');
		var evtSource = new EventSource(basedir+"projectTestsServerSend/?id={=$project->id}&entry={=$entry}&env="+$('#SelectEnv').val());
		evtSource.onmessage = function(m){
			var data=$.parseJSON(m.data),tr=$('#tr'+data.i);
			tr.children('td:eq(4)').html('<span class="icon '+(data.success?'tick':'cross')+'"></span> '+data.status);
			if(data.contentOk!==null) tr.children('td:eq(5)').html('<span class="icon '+(data.contentOk?'tick':'cross')+'"></span> '+(data.contentOk?'Le contenu correspond':'Le contenu ne correspond pas')+'.');
			//console.log(data);
			//list.append($('<li class="content"/>').attr('style',m.data.sbStartsWith('WARNING')?'color:orange':(m.data.sbStartsWith('ERROR')?'color:red':'')).text(m.data));
		};
		evtSource.addEventListener('close',function(){
			evtSource.close();
			a.show();
		});
		return false;
	}
	
	function updateType(i,select){
		var val=$(select);
	}
S.ready(function(){
	var i={=$i},form=$('#FormTests'),formAddTest=$('#FormAddTest'),table=form.find('table');
	form.find("li").sortable({
		placeholder: "ui-state-highlight",
		connectWith: ".connectedSortable",
		update:function(){ form.submit(); }
	}).disableSelection();
	form.ajaxForm("<? HHtml::urlEscape('/projectTests/save/'.$project->id.'/'.$entry) ?>");
	
	form.on('change','input,select',function(){form.submit()});
	
	formAddTest.sSubmit(function(f,c){
		c();
		var input=f.find('input:first'),url=input.val();
		if(!url){
			alert('Url vide');
			return;
		}
		table.append($('<tr id="tr'+(++i)+'"/>').append(
			$('<td class="w1"/>').append($('<a target="_blank" onclick="$(this).parent().children(\'div\').toggle()"/>').text(url).attr('href',url),$('<input name="tests['+i+'][url]" type="hidden"/>').attr('value',url)),
			$('<td/>'),
			$('<td/>').html('<select name="tests['+i+'][type]"><option value="200">200 OK</option><option value="301">301 Redirection</option><option value="404">404 Not Found</option></select>'),
			$('<td/>'),
			$('<td/>'),
			$('<td/>')
		));
		input.val('');
		form.submit();
	});
});
<? HHtml::jsInlineEnd() ?>