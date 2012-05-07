<?php
/** @TableAlias('splg') */
class ServerPlugin extends SSqlModel{
	public
		/** @Pk @AutoIncrement @SqlType('INTEGER') @NotNull
		 */ $id,
		/** @SqlType('INTEGER') @NotNull
		 */ $server_id,
		/** @SqlType('INTEGER') @NotNull
		 */ $plugin_id,
		/** @SqlType('VARCHAR(100)') @NotNull
		 */ $folder_name;


	public static $belongsTo=array(
		'Server','Plugin'
	);
}
