<?php new AjaxContentView(_t('Daemons')) ?>
{ife $servers}
{link 'Add a server','/servers'}
{else}
<table>
	<tr><th>Server</th><th>Is Alive</th><th>Actions</th></tr>
	{f $servers as &$server}
		<tr><td>{$server->name}</td><td>{if $server->isAlive}{icon enabled}{else}{icon disabled}{/if}</td><td>{if !$server->isAlive}{link 'Start','/site/startDaemon/'.$server->id}{else}{/if}</td></tr>
	{/f}
</table>
{/if}
