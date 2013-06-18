//include Lib('jquery.filetree');

var editor,hlLine,currentFile,modes,hasCtrlKey=false,lastSaveHistorySize=0,waitingFunction,dialogs,baseUrl;

$(document).ready(function(){
	baseUrl=baseUrl+'editor/'+projectId+'/'+projectType;
	dialogs={
		saveFile:$('#dialog-ask-save-file').dialog({autoOpen:false,resizable:false,height:170,modal:true,buttons:[
			{text:_t('No'),click:function(){$(this).dialog('close');waitingFunction()}},
			{text:_t('Cancel'),click:function(){$(this).dialog('close');}},
			{text:_t('Yes'),click:function(){$(this).dialog('close');saveFile(waitingFunction);}},
		]})
	};
	
	editor=CodeMirror($('#editorParent').get(0),{
		lineNumbers:true,indentWithTabs:true,indentUnit:8,
		onCursorActivity: function(){
			editor.setLineClass(hlLine,null);
			hlLine=editor.setLineClass(editor.getCursor().line, "activeline");
		},
		//onChange:function(){},
		onKeyEvent:function(i,e){//console.log(i);console.log(e);
			if(e.type == 'keydown'){
				if(e.keyCode == 17) hasCtrlKey=true;
				// Hook into F11
				else if(e.keyCode == 122 || e.keyCode == 27){
					e.stop();
					return toggleFullscreenEditing();
				// Hook into S
				}else if(hasCtrlKey && e.keyCode == 83){
					e.stop();
					saveFile();
				}
				
			}else if(e.type == 'keyup'){
				hasCtrlKey=false;
			}
		}
	});
	hlLine=editor.setLineClass(0, "activeline");

	$('#projectName').contextMenu({ menu:'projectMenu'},function(action, el, pos){
		if(action==='folder_add'){
			var folderName=prompt(_t('Folder name ?'));
			if(folderName) $.post(baseUrl+'FolderAdd',{folderName:folderName},function(){loadFileTree();});
		}
	});

	loadFileTree();
});



/* EDITOR FUNCTIONS */
function loadFileTree(){
	$('#fileTree').fileTree({
		script:baseUrl+'FileTree',
		onFolderExpanded:function($f){
			$f.find(".jqueryFileTree li.directory").contextMenu({ menu:'folderMenu'},function(action, el, pos){
				if(action==='file_add'){
					var fileName=prompt(_t('File name ?'));//Attachment.php
					if(fileName){
						var aFolder=el.find('a:first');
						$.post(baseUrl+'FileAdd',{dir:aFolder.attr('rel'),fileName:fileName},function(){el.hasClass('collapsed')?aFolder.click():aFolder.click().delay(50).click();});
					}
				}
			});
		}
	},function(file){
		waitingFunction=function(){
			$.post(baseUrl+'FileContent',{file:file},function(data){
				lastSaveHistorySize=0;
				currentFile=file;
				var idxExt=file.lastIndexOf(".")+1;ext=idxExt>1?file.substring(idxExt).toLowerCase():false;
			
				switch(ext){
					case 'php': editor.setOption('mode','application/x-httpd-php'); editor.setOption('matchBrackets',true); break;
					case 'html': editor.setOption('mode','text/html'); editor.setOption('matchBrackets',false); break;
					case 'css': editor.setOption('mode','text/css'); editor.setOption('matchBrackets',true); break;
					case 'js': editor.setOption('mode','text/javascript'); editor.setOption('matchBrackets',true); break;
					case 'json': editor.setOption('mode','application/json'); editor.setOption('matchBrackets',true); break;
					case 'sql': editor.setOption('mode','text/x-plsql');  editor.setOption('matchBrackets',false); break;
					default: editor.setOption('mode',''); editor.setOption('matchBrackets',false);
				}
				editor.setValue(data);
				hlLine=editor.setLineClass(0, "activeline");
			});
		};
		if(editor.historySize().undo!==lastSaveHistorySize){
	  		//console.log(dialogs.saveFile);
	  		dialogs.saveFile.dialog('open');
		}else waitingFunction();
	});
}



function saveFile(afterSave){
	if(currentFile){
		lastSaveHistorySize=editor.historySize().undo;
		$.post(baseUrl+'SaveFileContent',{file:currentFile,content:editor.getValue()},afterSave);
	}else{
		alert('no current file');
		afterSave();
	}
}

function toggleFullscreenEditing(){
	var editorDiv = $('.CodeMirror-scroll,#editorParent,#page');
	if (!editorDiv.hasClass('fullscreen')) {
		toggleFullscreenEditing.beforeFullscreen = { height: editorDiv.height(), width: editorDiv.width() }
		editorDiv.addClass('fullscreen');
		editorDiv.height('100%');
		editorDiv.width('100%');
		editor.refresh();
	}else{
		editorDiv.removeClass('fullscreen');
		editorDiv.height(toggleFullscreenEditing.beforeFullscreen.height);
		editorDiv.width(toggleFullscreenEditing.beforeFullscreen.width);
		editor.refresh();
	}
}

window.onbeforeunload=function(){
	var history=editor.historySize();
	if(history.undo!==lastSaveHistorySize)
		return "You have attempted to leave this page. If you have made any changes to the fields without clicking the Save button, your changes will be lost. Are you sure you want to exit this page?";
}