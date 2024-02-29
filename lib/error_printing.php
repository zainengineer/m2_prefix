<?php
namespace zain_custom\lib;
class ErrorPrinting {
    static function printTrace(array $trace)
    {
        $aReplace = array();

        foreach ($trace as $key => &$call) {
            if (empty($call['file']) || empty($call['file'])) {
                $call = array('location' => 'missing in trace') + $call;
                continue;
            }
            $file = $call['file'];
            $line = $call['line'];
            $phpStormLink = self::getPhpStormLine($file, $line);
            unset($call['file']);
            unset($call['line']);

            $call = array('location' => 'ZainReplaceIt' . $key) + $call;
            $aReplace[$call['location']] = $phpStormLink;
        }

        $output = @d($trace);
        //using strtr instead of replace as ZainReplace1 and ZainReplace10 will conflict causing incorrect replacement
        $output = strtr($output,$aReplace);
        echo $output;
    }

    public static function getPhpStormLine($vFullPath, $line)
    {
        $vDisplayPath = self::removeBasePath($vFullPath);
        $vActualPath = self::isVM()? $vDisplayPath : $vFullPath;
        return \ZActionDetect::getPhpStormLink($vActualPath,$line,$vDisplayPath);
    }

    public static function isVM()
    {
        if (file_exists('/vagrant')){
            return true;
        }
        $vContents = @file_get_contents('/proc/1/cgroup');
        if (strpos($vContents,'memory:/docker/')){
            return true;
        }
        return false;
    }

    public static function removeBasePath($file)
    {
        $vRootDirectory = $_SERVER['DOCUMENT_ROOT'];
        $iMaxCount = 20;
        $i=0;
        while(true)
        {
            if ($i>$iMaxCount){
                return $file;
            }
            if (strpos($file,$vRootDirectory)===0){
                return true;
            }
            $vRootDirectory = dirname($vRootDirectory);
        }
    }
    public static function showException(\Exception $e)
    {
        echo "Go to error: "  . $e->getMessage() . " " . \zain_custom\lib\ErrorPrinting::getPhpStormLine($e->getFile(),$e->getLine()) . "<br/>\n";
        if ($e instanceOf \ReflectionException ){
            if ($previous = $e->getPrevious()){
                if ($previous instanceof \Exception){
                    echo self::showException($previous);
                }
                else{
                    var_dump($previous);
                }
            }
        }
    }

}
