<?php
/** @Db('default') @TableAlias('w') */
class Workspace extends SSqlModel{
	public
		/** @Pk @AutoIncrement @SqlType('INTEGER') @NotNull
		 */ $id,
		/** @SqlType('VARCHAR(200)') @NotNull
		 */ $name,
		/** @SqlType('VARCHAR(255)') @NotNull
		 */ $projects_dir,
		/** @SqlType('VARCHAR(255)') @NotNull
		 */ $db_name;
}