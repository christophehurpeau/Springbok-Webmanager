<?php
/** @TableAlias('plgp') */
class PluginPath extends SSqlModel{
	public
		/** @Pk @AutoIncrement @SqlType('INTEGER') @NotNull
		 */ $id,
		/** @SqlType('VARCHAR(200)') @NotNull
		 */ $name,
		/** @SqlType('VARCHAR(200)') @NotNull
		 */ $path;
}
