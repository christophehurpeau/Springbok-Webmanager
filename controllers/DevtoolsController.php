<?php
Controller::$defaultLayout='dev_tools';
class DevtoolsController extends Controller{
	/** */
	function index(){
		self::render();
	}
	
	/** */
	function colors($hexColor){
		if($hexColor===NULL) $hexColor='888888';
		self::set('colors',UColors::darkerAndLighterShadesWithForeground($hexColor));
		self::render();
	}
	
	/** */
	function pt_px(){
		self::render();
	}
	
	/** */
	function jqueryui(){
		self::render();
	}
	
	/* @SimpleAction('css') */
}
