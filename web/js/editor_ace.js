//include Lib('jquery.filetree');

var editor,currentFile,modes;
window.onload = function() {
    editor = ace.edit("editor");
    editor.setScrollSpeed(4);
    editor.setTheme("ace/theme/dawn");
    
    var canon = require("pilot/canon");
    
    var PhpMode = require("ace/mode/php").Mode,
    	HtmlMode = require("ace/mode/html").Mode,
    	CssMode = require("ace/mode/css").Mode,
    	JavaScriptMode = require("ace/mode/javascript").Mode,
    	TextMode = require("ace/mode/text").Mode,
    
    	UndoManager = require("ace/undomanager").UndoManager;
    
    modes = {
    	php:new PhpMode(),
    	html:new HtmlMode(),
    	css:new CssMode(),
    	js:new JavaScriptMode(),
    	text:new TextMode()
    };
    
    editor.getSession().setMode(modes.php);
    editor.getSession().setUseWrapMode(false);
    editor.renderer.setShowPrintMargin(false);
    editor.getSession().setUndoManager(new UndoManager());
    
	canon.addCommand({
		name: "save",
		bindKey:{
			win: "Ctrl-S",
			mac: "Command-S",
			sender: "editor"
		},
		exec: function(){
			if(currentFile)
				$.post(basedir+'projectEditor/saveFileContent/'+projectId,{file:currentFile,content:editor.getSession().doc.getValue()});
			else alert('no current file');
		}
	});
};


$(document).ready(function(){
	$('#fileTree').fileTree({
			script:basedir+'projectEditor/fileTree/'+projectId,
			onFolderExpanded:function($f){
				$f.find(".jqueryFileTree li.directory").contextMenu({ menu:'folderMenu'},function(action, el, pos){
					switch (action){
						case "delete":
							//Popup Delete Confirmation - included in demo and in download
							break;
						case "insert":
							//Popup Insert Dialog- included in demo and in download
							break;
						case "edit":
							//Popup Edit Dialog
							break;
					}
				});
			}
	  },function(file){ 
		$.post(basedir+'projectEditor/fileContent/'+projectId,{file:file},function(data){
			currentFile=file;
			var idxExt=file.lastIndexOf(".")+1;ext=idxExt>1?file.substring(idxExt).toLowerCase():false;
			
			switch(ext){
				case 'php': editor.getSession().setMode(modes.php);
				case 'html': editor.getSession().setMode(modes.html);
				case 'css': editor.getSession().setMode(modes.css);
				case 'js': editor.getSession().setMode(modes.js);
				default: editor.getSession().setMode(modes.text);
			}
			editor.getSession().doc.setValue(data);
			setTimeout('editor.gotoLine(1)',50);
		});
	});
});