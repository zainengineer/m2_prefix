<?php
xdebug_break();
$debugIt = 1;
echo date('c') . "\n<br/>";
$xdebugMode = ini_get('xdebug.mode');;
d($xdebugMode);
if ($xdebugMode!=='debug'){
    d("xdebug needs debug mode");
}
if (function_exists('xdebug_break')){xdebug_break();die('break after xdebug');} else{die('xdebug does not exist');}
