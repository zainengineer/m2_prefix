<?php

Class ShowExceptionAsNormalMessage extends \Exception
{
    public $errorData = [];
    public $rawMessage = '';
    public $collapse = false;
}

Class ZActionDetect
{
    static function listMethods($instanceName)
    {
        $error = new \ShowExceptionAsNormalMessage();
        $reflectionClass = new \ReflectionClass($instanceName);
        $error->collapse = true;
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $methodList = array_map(function (\ReflectionMethod $method) {
            $name = $method->getName();
            return ($name == '__construct') ? [] : ['name'=>$name,'method'=>$method];
        }, $methods);
        $methodList = array_filter($methodList);
        $methodList = array_column($methodList,'method','name');
        $error->errorData = [];
        if ($docBlock = $reflectionClass->getDocComment()){
            if (!strpos($docBlock,'* To replace')){
                $error->errorData['doc'] = $docBlock;
            }
        }

        $error->errorData["available Methods for $instanceName are"] = array_keys($methodList);
        $error->rawMessage = self::fillActionUrls($methodList,$reflectionClass->getFileName());
        throw $error;
    }
    public static function callMethod($instance)
    {
        $baseUrl = $_SERVER['REQUEST_URI'];
        echo "<a href='/?op=snippets'>Snippets</a>\n<br/><br/>\n";
        $timeStart = microtime(true);
        $instanceName = get_class($instance);
        if (!isset($_GET['action'])) {
            self::listMethods($instanceName);
        }
        $action = $_GET['action'] ?? 'main';
        $methodName = $action ?: 'main';
        $actionLabel = "$instanceName->$methodName()";
        $vMessage = "Unable to call  $actionLabel";
        if (!is_callable([$instance, $methodName])){
            self::displayError($vMessage);
            echo "<br/><br/>";
            //auto throws error to get out of the loop
            self::listMethods($instanceName);
        }
        echo self::indexLink() . "<br/><br/>\n";
        $r = new \ReflectionMethod($instanceName, $methodName);
        echo self::phpStormMethodLinks($r, $actionLabel) . "<br/>\n";
        $params = $r->getParameters();
        $aArguments = [];
        foreach ($params as $param) {
            //$param is an instance of ReflectionParameter
            $paramName = $param->getName();
            if (!isset($_GET[$paramName])) {
                if ($param->isOptional()) {
                    $argValue = $param->getDefaultValue();
                }
                else {
                    throw new \ShowExceptionAsNormalMessage("$paramName parameter missing for $actionLabel");
                }
            }
            else {
                $argValue = $_GET[$paramName];
            }
            $aArguments[] = $argValue;
        }
        try {
            $aReturn = call_user_func_array([$instance, $methodName], $aArguments);
        }
        catch (\Exception $e){
            $aReturn = $e;
        }

        $timeTaken = microtime(true) -$timeStart;
        $aReturn = [
            $actionLabel => $aReturn,
            'time' => number_format($timeTaken,2),
        ];
        return $aReturn;
    }
    public static function showOutput(string $className,\MagentoInc $magentoIncGiven)
    {
        if (empty($GLOBALS['just_include_snippet_class'])) {

            $instanceName = $magentoIncGiven->getObjectFromName($className);

            try {
                $outcome = ZActionDetect::callMethod($instanceName);
                $functionCalled = key($outcome);
                $valueReturned = current($outcome);
                if (is_object($valueReturned) && ($valueReturned instanceof \Exception)) {
                    d($valueReturned);
                } else {
                    !d($valueReturned);
                }
                echo "Function Called: <span style='background: #e0eaef'>$functionCalled</span><br/>\n";
                if (isset($outcome['time'])) {
                    $timeTakenByMethod = $outcome['time'];
                    echo "Time Taken: <span style='background: #e0eaef'><b>$timeTakenByMethod</b></span><br/>\n";
                }
            } catch (\ShowExceptionAsNormalMessage $e) {
                $message = $e->errorData ?: $e->getMessage();
                if ($e->rawMessage) {
                    echo $e->rawMessage;
                }
                if ($e->collapse){
                    d($message);
                }
                else{
                    !d($message);
                }
            }
        }
    }
    static protected function displayError($vMessage)
    {
        echo "<div style='color: red'>$vMessage</div>";
    }
    public static function indexLink() : string
    {
        $url = $_SERVER['REQUEST_URI'];
        $parts = parse_url($url);
        parse_str($parts['query'],$aQuery);
        $op = $aQuery['op'];
        $query = "op=$op";

        $url =  $parts['path'] . '?' . $query;
        $link = "<a href='$url'>list all</a>";
        return $link;
    }
    public static function phpStormMethodLinks(\ReflectionMethod $method,
                                               string $label,
                                                bool $includeEnd = true): string
    {
        $firstLine = $method->getStartLine() +2;
        $lastLine = $method->getEndLine()-2;
        $fileName = $method->getFileName();
        $fileName = self::removeFilePrefix($fileName);
        //<a class="kint-ide-link" href="http://localhost:8091/?message=/vagrant/pub/zain_custom/lib/action_detect.php:63">&lt;ROOT&gt;/zain_custom/lib/action_detect.php:63</a>
        $end = $includeEnd ? "  ---- <a href='http://localhost:8091/?message=$fileName:$lastLine'>end</a>": '';
        return "<a href='http://localhost:8091/?message=$fileName:$firstLine'>$label</a>$end";
    }
    public static function fileLink($fileName): string
    {
        $fileName = self::removeFilePrefix($fileName);
        return "Navigate to: <a href='http://localhost:8091/?message=$fileName'>$fileName</a>";
    }
    protected static function removeFilePrefix(string $fileName):string
    {
        $vPrefix = '/vagrant/';
        if (strpos($fileName,$vPrefix) === 0){
            $fileName = substr($fileName,strlen($vPrefix));
        }
        else{
//            $basePath = dirname(dirname(dirname(__DIR__)));
            $basePath = dirname(__DIR__);
            $fileName = ltrim(str_replace($basePath,'',$fileName),'/');
        }
        return $fileName;
    }

    public static function fillActionUrls(array $actions,$fileName)
    {
        $baseUrl = $_SERVER['REQUEST_URI'];
        $actionLinks = array_map(function (string $functionName,$method) use ($baseUrl) {
            $methodLink = self::phpStormMethodLinks($method,'goto',false);
            return "<a href='$baseUrl&action=$functionName'>$functionName</a>  <small>$methodLink</small>" ;
        },array_keys($actions), $actions);
        if ($fileName){
            $actionLinks[] = self::fileLink($fileName) . '<small>' .   '</small>';
        }
        return implode("<br/>\n", $actionLinks);
    }
}