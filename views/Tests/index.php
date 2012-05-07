<?php $v=new AjaxContentView(_t('Tests')) ?>

<ul>
{f $tests as $test}
	<li>{link $test,'/tests/test/'.$test}</li>
{/f}
</ul>