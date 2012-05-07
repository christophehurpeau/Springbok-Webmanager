<?php
/*** @TableAlias('plgPrj') */
class PluginProject extends SSqlModel{
	public
		/** @Pk @SqlType('INTEGER') @NotNull
		 */ $plugin_id,
		/** @Pk @SqlType('INTEGER') @NotNull
		 */ $project_id,
		/** @SqlType('INTEGER') @Default(1) @NotNull
		 */ $position;
	
	public static $belongsTo=array('Plugin','Project');
}
