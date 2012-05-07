<?php $v=new AjaxContentView(_t('Test:').' '.$name) ?>

<div class="content">
<? HHtml::tag('pre',array(),print_r($res,true)) ?>
</div>