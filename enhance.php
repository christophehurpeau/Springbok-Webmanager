<?php
ini_set('display_errors',1);
error_reporting(E_ALL | E_STRICT);
define('DS',DIRECTORY_SEPARATOR);
define('APP',__DIR__.'/dev/');
define('CORE',dirname(__DIR__).'/core/dev/');
define('CLIBS',dirname(CORE).'/libs/dev/');
define('ENV',include dirname(CORE).DS.'env.php');

include CORE.'base'.DS.'base.php';
include CORE.'enhancers'.DS.'EnhanceApp.php';
include CORE.'utils/UExec.php';
include CORE.'utils/UEncoding.php';

$f=new Folder(__DIR__.DS.'tmp_dev'); if($f->exists()) $f->delete();
$f=new Folder(__DIR__.DS.'tmp_prod'); if($f->exists()) $f->delete();

$instance=new EnhanceApp(__DIR__);
$instance->process(true);
