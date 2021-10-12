<?php
$debugIt = 1;
d(1);
if (function_exists('xdebug_break')){xdebug_break();die('break after xdebug');} else{die('xdebug does not exist');}