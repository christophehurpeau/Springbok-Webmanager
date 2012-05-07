<?php $v=new AjaxContentView('Dev Tools') ?>

<?php $form=HForm::create(NULL); ?>
<? $form->input('hexColor',array('label'=>false)) ?>
<? $form->end('Ok') ?>

<ul class="devtools_colors">
{f $colors as $key=>$color}
	<li style="background:#<? $color['bg'] ?>;color:#<? $color['fg'] ?>{if $key==0};font-weight:bold{/if}"><? '#'.$color['bg'] ?></li>
{/f}
</ul>
