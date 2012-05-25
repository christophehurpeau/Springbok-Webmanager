<?php
Controller::$defaultLayout='dev_tools';
class TestsController extends Controller{
	/** */
	function index(){
		$tests=array_diff(get_class_methods(__CLASS__),array('index','test','push'),get_class_methods('Controller'));
		mset($tests);
		render();
	}
	
	/** */
	function test($name){
		mset($name);
		set('res',call_user_func(array('self',$name)));
		render();
	}
	
	/** */
	function rand(){
		/*$array=array('a','b');
		debugVar(array_rand($array,2));*/
		debugVar(UGenerator::randomCode(12));
	}
	
	/** */
	function testpush(){
		/*self::allowFlush();
		$i=10;
		while($i-->0){
			self::push($i);
			sleep(1);
		}*/
	}
	/** */
	function testmysqllongquery(){
		$db=EMCategory::$__modelDb;
		set_time_limit(2); // 2 secondes max pour exécuter la requete
		$db->doUpdate('SELECT SLEEP(90)');
	}
	
	
    private static function shuffle(){
        return UProfiling::compare(30000,function(){
        	$str=str_shuffle('abcdefghijklmnopqrstuvwxyz');
            return str_split($str);
        },function(){
        	$array=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','p','q','r','s','t','u','v','x','y','z');
            return shuffle($array);
        });
    }
	
    
	private static function defaultOptions(){
		return array(
		UProfiling::compare(9000,function(){
			$options=array();
            return $options+array('startsWith'=>false);
        },function(){
			$options=array('startsWith'=>false);
            return $options+array('startsWith'=>false);
        },function(){
        	$options=array();
			if(!isset($options['startsWith']))$options['startsWith']=false;
            return $options;
        },function(){
			$options=array('startsWith'=>false);
			if(!isset($options['startsWith']))$options['startsWith']=false;
            return $options;
        })
		,
		UProfiling::compare(9000,function(){
			$options=array();
            return $options+array('lioptions'=>array(),'linkoptions'=>array(),'startsWith'=>false);
        },function(){
			$options=array('startsWith'=>false);
            return $options+array('lioptions'=>array(),'linkoptions'=>array(),'startsWith'=>false);
        },function(){
        	$options=array();
			if(!isset($options['startsWith']))$options['startsWith']=false;
			if(!isset($options['lioptions']))$options['lioptions']=array();
			if(!isset($options['linkoptions']))$options['linkoptions']=array();
            return $options;
        },function(){
			$options=array('startsWith'=>false);
			if(!isset($options['startsWith']))$options['startsWith']=false;
			if(!isset($options['lioptions']))$options['lioptions']=array();
			if(!isset($options['linkoptions']))$options['linkoptions']=array();
            return $options;
        })
		);
	}
	
    private static function md5(){
        return UProfiling::compare(3000,function(){
            return md5_file('/home/christophe/TODO');
        },function(){
            return UExec::exec('md5sum /home/christophe/TODO');
        });
    }
	
	private static function botlist(){
		return UProfiling::compare(7000,function(){
			$botlist=array(
			'bot',
			//'Googlebot',
			'Google Web Preview', // Google - www.google.com
			//'msnbot',
			'Yahoo',
			//'VoilaBot',
			//'WebCrawler',
			'crawler',
			
			'Scooter', // Alta Vita - www.altavista.com
			//'Ask Jeeves\/Teoma', // Ask - www.ask.com & Teoma - ww.teoma.com
			'Lycos_Spider_\(T-Rex\)', // Lycos - www.lycos.com
			'Slurp', // Inktomi - www.inktomi.com
			'HenryTheMiragorobot', // Mirago - www.mirago.com
			'FAST\-WebCrawler', // AlltheWeb - www.alltheweb.com
			'W3C_Validator',
			
			'Teoma', 'alexa', 'froogle', 'inktomi',
			'looksmart', 'URL_Spider_SQL', 'Firefly', 'NationalDirectory',
			'Ask Jeeves', 'TECNOSEEK', 'InfoSeek', 
			'www.galaxy.com','appie', 'FAST', 'WebBug', 'Spade', 'ZyBorg', 'rabaz',
			'Baiduspider', 'Feedfetcher-Google', 'TechnoratiSnoop',
			'Mediapartners-Google', 'Sogou web spider',
			'Butterfly','Twitturls','Me.dium','Twiceler'
		);
			foreach($botlist as $bot) if(stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false) return true;
		},function(){
			return (bool)preg_match('/'./* EVAL "'".implode('|',array(
			'bot',
			//'Googlebot',
			'Google Web Preview', // Google - www.google.com
			//'msnbot',
			'Yahoo',
			//'VoilaBot',
			//'WebCrawler',
			'crawler',
			
			'Scooter', // Alta Vita - www.altavista.com
			//'Ask Jeeves\/Teoma', // Ask - www.ask.com & Teoma - ww.teoma.com
			'Lycos_Spider_\(T-Rex\)', // Lycos - www.lycos.com
			'Slurp', // Inktomi - www.inktomi.com
			'HenryTheMiragorobot', // Mirago - www.mirago.com
			'FAST\-WebCrawler', // AlltheWeb - www.alltheweb.com
			'W3C_Validator',
			
			'Teoma', 'alexa', 'froogle', 'inktomi',
			'looksmart', 'URL_Spider_SQL', 'Firefly', 'NationalDirectory',
			'Ask Jeeves', 'TECNOSEEK', 'InfoSeek', 
			'www.galaxy.com','appie', 'FAST', 'WebBug', 'Spade', 'ZyBorg', 'rabaz',
			'Baiduspider', 'Feedfetcher-Google', 'TechnoratiSnoop',
			'Mediapartners-Google', 'Sogou web spider',
			'Butterfly','Twitturls','Me.dium','Twiceler'
		))."'" /EVAL *//* HIDE */''/* /HIDE */.'/i',$_SERVER['HTTP_USER_AGENT']);
			return false;
		});
	}
	
	private static function date_vs_strftime(){
		return UProfiling::compare(70000,function(){
			return strftime('%Y-%m-%d %H:%M:%S');
		},function(){
			return date('Y-m-d H:i:s');
		},function(){
			return strftime('%F %T');
		},function(){
			$dt=new DateTime();
			return $dt->format('Y-m-d H:i:s');
		});
	}
	
	private static function gfc_vs_incl(){
		$filename=dirname(CORE).'/env.php';
		return UProfiling::compare(90000,function() use(&$filename){
			return file_get_contents($filename);
		},function() use(&$filename){
			return include $filename;
		});
	}
	
	private static function copy_array(){
		$array=array(1=>'val 1','2'=>'val 2','3'=>'val 3',4=>'val 4',5=>'val 5','6'=>'val 6');
		return UProfiling::compare(70000,function() use(&$array){
			$data=array();
			foreach($array as $k=>$v) $data[$k]=$v;
			return $data;
		},function() use(&$array){
			return array_map(array('TestsController','return_value'),$array);
		},function() use(&$array){
			return array_map(function($v){return $v;},$array);
		},function() use(&$array){
			reset($array);
			$data=array();
			while(($v=current($array))!==false){
				$data[key($array)]=$v;
				next($array);
			}
			return $data;
		},function() use(&$array){
			reset($array);
			$data=array();
			$v=current($array);
			while($v!==false){
				$data[key($array)]=$v;
				$v=next($array);
			}
			return $data;
		});
	}
	
	public static function return_value($v){return $v;}
	
	
	private static function logs(){
		$exception=new Exception;
		$message=$exception->__toString();
		if(isset($_SERVER['REQUEST_URI']))
			$message.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
		return UProfiling::compare(1000,function() use(&$message){
			//CLogger::
		},function() use(&$message){
			
		});
	}
	
	public static function comp_string(){
		return array(
			'levenshtein'=>levenshtein($_GET['1'],$_GET['2']),
			'dice'=>HString::dice($_GET['1'],$_GET['2']),
			'jaroWinkler'=>HString::jaroWinkler($_GET['1'],$_GET['2'])
		);
	}
	
	public static function form(){
		return UProfiling::compare(40000,function(){
			ob_start();
			$form=HForm::create('Project',array('action','/projects/add'));
			echo $form->input('name');
			$form->end(true,array(),array());
			ob_end_clean();
		},function(){
			ob_start();
			echo $form=Project::Form()->action('/projects/add');
			echo $form->input('name');
			echo $form->end();
			ob_end_clean();
		});
		
	}
}
