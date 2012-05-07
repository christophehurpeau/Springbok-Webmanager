<?php
/**
 * @TableAlias('pl')
 */
class ProjectLang extends SSqlModel{
	public
		/** @Pk @SqlType('VARCHAR(10)') @NotNull
		 */ $name,
		/** @Pk @SqlType('INTEGER') @NotNull
		 */ $project_id;

	public static $belongsTo=array(
		'Project'=>array(),
	);
	
}