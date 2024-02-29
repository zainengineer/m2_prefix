<?php
namespace zain_custom\lib;
require_once __DIR__ . '/error_printing.php';
function __fatalHandler()
{
    $error = error_get_last();
//check if it's a core/fatal error, otherwise it's a normal shutdown
    if ($error !== NULL && in_array($error['type'], array(E_ERROR,E_NOTICE, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING,E_RECOVERABLE_ERROR))) {
        $file = isset($error['file']) ? $error['file'] : false;
        if ($file=='Unknown'){
            return ;
        }
        if (isDebugEval($file)){
            return ;
        }
        breakIfNoErrorSuppression($file);
        if (php_sapi_name() == "cli") {
            //this is a dummy line so breakpoint can be placed on this line
            $debugThisLine = 1;
        }
        if (!function_exists('xdebug_break')){
            echo "<pre>";
        }
        var_dump($error);
        showTrace();
        fixXdebugLinks();
    }
}
function fixXdebugLinks()
{
    ?>
    <script>
        (() => {
             let links = document.querySelectorAll("table.xdebug-error td a");
             let dumpLinks = document.querySelectorAll("pre.xdebug-var-dump a");
             [...links,...dumpLinks].map((link)=>link.addEventListener('click', (e) => {
                 e.preventDefault();
                 console.log('fetching ',e.currentTarget.href );
                 fetch(e.currentTarget.href);
                 return false;
             } ));
        })();


    </script>
<?php

}
function showTrace()
{
    if (function_exists('xdebug_get_function_stack')){
        ob_start();
        //echo "now internal stack";
        //Not working in PHP 7
        //xdebug_print_function_stack();
        //not working in PHP 7
//        ErrorPrinting::printTrace(xdebug_get_function_stack());
//        $vErrorDump = ob_get_clean();
        var_dump(xdebug_get_function_stack());
    }
    else{
        $vErrorDump = 'Could not get error dump because xdebug not enabled';
        echo $vErrorDump;
    }
}
function breakExecution()
{
    if (function_exists('xdebug_break')){
        xdebug_break();
    }
}
function breakIfNoErrorSuppression($errorFile)
{
    /**
     * Error suppression was used, but it could be a fatal error
     * No Good work around as we can't be sure if it is a normal error or fatal error
     * error_get_last() could be last error which did not cause fatal
     * or it is fatal error which stopped code flow
     * both have same data in error_get_last()
     * So just using xdebug_break as a notice / help for developer to find the issue.
     * Normally it happens when @include a file with syntax error (typed random values)
     */
    //
    //
    if (!error_reporting()){
        if (!isDebugEval($errorFile)) {
            breakExecution();
            if (!empty($_ENV['ignore_last_error'])){
                return;
            }
        }
    }
}
function isDebugEval($errorFile)
{
    return (strpos($errorFile, 'xdebug://debug-eval') !== false);
}

register_shutdown_function('zain_custom\lib\__fatalHandler');
