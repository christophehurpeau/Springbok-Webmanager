<?php new AjaxContentView(_t('Server:').' '.$server->name,'servers'); ?>
{include _viewmenu.php}
<div class="content">
<?php foreach($server as $key=>$value): ?>
	<div>
		<?= _tF('Server',$key) ?> : <?= $value ?>
	</div>
<?php endforeach; ?>
</div>
<div class="content sepTop">
	<i><?= _t('Actions:') ?></i>
	<ul>
		<li><? HHtml::link('Test SSH Connection','?testSshConnection=1') ?></li>
	</ul>
</div>
<div class="content sepTop">
	First time ? Execute : sudo su www-data -c <?= escapeshellarg($basicCommand) ?><br />
	and <b>accept fingerprint</b>
</div>
