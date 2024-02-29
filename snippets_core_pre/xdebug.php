<?php
$debugIt = 1;
echo date('c') . "\n<br/>";
$xdebugMode = ini_get('xdebug.mode');;
xdebug_break();
!d($xdebugMode);
array_reduce(explode("\n",php_ini_scanned_files()),
    fn( $carry,string $iniLine) => strpos($iniLine,'xdebug') && !d(trim($iniLine,','))) ;

if (strpos($xdebugMode,'debug')===false){
    !d("xdebug needs debug mode");
}
if (function_exists('xdebug_break')){xdebug_break();die('break after xdebug');} else{die('xdebug does not exist');}
