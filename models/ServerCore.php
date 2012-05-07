<?php
/** @TableAlias('sc') */
class ServerCore extends SSqlModel{
	public
		/** @Pk @AutoIncrement @SqlType('INTEGER') @NotNull
		 */ $id,
		/** @SqlType('INTEGER') @NotNull
		 */ $server_id,
		/** @SqlType('VARCHAR(30)') @NotNull
		 */ $version,
		/** @SqlType('VARCHAR(100)') @NotNull
		 */ $path;


	public static $belongsTo=array(
		'Server'
	);
	public static $hasMany=array(
		'Deployment'
	);
}
