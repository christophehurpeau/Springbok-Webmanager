<? HHtml::doctype() ?>
<html>
	<head>
		<? HHtml::metaCharset() ?>
		<?php
			HHead::title($layout_title);
			HHead::linkCssAndJs();
			HHead::jsI18n();
			HHead::favicon();
			HHead::display();
		?>
	</head>
	<body>{=$layout_content}</body>
</html>