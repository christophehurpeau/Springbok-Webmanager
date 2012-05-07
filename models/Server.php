<?php
/** @TableAlias('s') */
class Server extends SSqlModel{
	public
		/** @Pk @AutoIncrement @SqlType('INTEGER') @NotNull
		 */ $id,
		/** @SqlType('VARCHAR(200)') @NotNull
		 */ $name,
		/** @SqlType('VARCHAR(100)') @NotNull
		 */ $host,
		/** @SqlType('VARCHAR(50)') @Null
		 */ $user,
		/** @SqlType('VARCHAR(255)') @Null
		 */ $pwd,
		/** @SqlType('VARCHAR(255)') @NotNull
		 */ $projects_dir,
		/** @SqlType('VARCHAR(255)') @Null
		 */ $plugins_dir,
		/** @SqlType('VARCHAR(255)') @NotNull
		 */ $core_dir,
		/** @SqlType('VARCHAR(20)') @NotNull @Default('prod')
		 */ $env_name;
	
	public $deployments,$cores;
	public static $hasMany=array('Deployment','ServerCore'=>array('dataName'=>'cores'));
	
	
	public function ssh(){
		return array(
			'user'=>$this->user,'host'=>$this->host
		);
	}
	
	public function sshOptions(){
		return array(
			'host'=>$this->host,'user'=>$this->user,
			'key_file'=>DATA.'ssh/'.$this->id.'-key',
			'known_hosts_file'=>DATA.'ssh/'.'known_hosts',
			'passphrase'=>USecure::decryptAES($this->pwd)
		);
		
	}
	
	public function deployCore($simulation=false,$force=false){
		$sc=ServerCore::findOneIdAndPathByServer_idAndVersion($this->id,Springbok::VERSION);
		
		if($sc===false || $force){
			$res='-- CORE VERSION IS NOT UP-TO-DATE ON SERVER --'.PHP_EOL;
			
			$sc_path='springbok-'.date('Y-m-d');
			if($id=ServerCore::findValueIdByServer_idAndPath($this->id,$sc_path))
				ServerCore::updateOneFieldByPk($id,'version',Springbok::VERSION);
			else{
				$sc=new ServerCore();
				$sc->server_id=$this->id;
				$sc->version=Springbok::VERSION;
				$sc->path=$sc_path;
				$sc->insert();
			}
			
			$options=array('simulation'=>$simulation,'exclude'=>array('.svn/','/enhance_def.php'),'ssh'=>$this->sshOptions());
			$res.=UExec::rsync(dirname(CORE).'/libs/',$this->core_dir.'/libs/',$options);
			$res.=UExec::rsync(dirname(CORE).'/prod/',$this->core_dir.DS.$sc_path.DS,$options);
		}else $res='This core is already up-to-date'.PHP_EOL;
		return $res;
	}
/*
	public function connect(){
		include CORE.'libs/phpseclib/Net/SSH2.php';
		include CORE.'libs/phpseclib/Crypt/RSA.php';
		$key = new Crypt_RSA();
		$key->setPassword(USecure::decryptAES($this->pwd));
		$key->loadKey(file_get_contents($this->getPrivKeyFile()));
		$ssh = new Net_SSH2($this->host);
		if(!$ssh->login($this->user,$key)) return false;
		return $ssh;
	}
*/
}