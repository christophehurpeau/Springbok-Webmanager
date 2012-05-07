<?php
/** @TableAlias('scr') */
class ServerCron extends SSqlModel{
	public
		/** @Pk @SqlType('INTEGER') @NotNull
		 */ $server_id,
		/** @Pk @SqlType('VARCHAR(30)') @NotNull
		 */ $deployment_id,
		/** @SqlType('TEXT') @NotNull
		 */ $value;

}
