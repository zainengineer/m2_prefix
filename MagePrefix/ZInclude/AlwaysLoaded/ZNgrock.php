<?php

namespace MagePrefix\ZInclude\AlwaysLoaded;

use MagePrefix\ZInclude\Traits\SingletonTrait;

class ZNgrock
{
    use SingletonTrait;
    public function replaceHostIfNeeded()
    {
        if (!isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            return;
        }
        $forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'];
        if (strpos($_SERVER['HTTP_HOST'], '.ngrok.io')) {
            return;
        }
        if (strpos($_SERVER['HTTP_HOST'], 'ngrok')) {
            return;
        }
        $_SERVER['HTTP_ORIGINAL_Z_HOST'] = $_SERVER['HTTP_HOST'];
        $_SERVER['HTTP_HOST'] = $forwardedHost;
    }
    static function getFlagPath(): string
    {
        return dirname(dirname(__DIR__)) . '/pre_ngrok_base_url.flag.json';
    }
    static function ngrockConfigChangeRequired()
    {

        $httpHost = $_SERVER['HTTP_HOST'];
        $ngrokInDomain = strpos($httpHost, '.ngrok.io') ? true : false;
        $ngrokInDomain = ($ngrokInDomain ||  strpos($httpHost, '.ngrok-free.app')) ? true : false;
        $ngrokMessage = false;
        if ($ngrokInDomain){
            $ngrokMessage = true;
            $flagPath = self::getFlagPath();
            $flagExists = file_exists($flagPath);
            if ($flagExists){
                $ngrokMessage = false;
                $conents = @json_decode(@file_get_contents($flagPath),true);
                $disabled =$conents['disabled']?? false;
                if ($disabled){
                    $ngrokMessage = true;
                }
            }
        }
        //only one is true
        if ($ngrokMessage) {

            echo "from:zain_custom<br/>\n";
            echo "Does not look your flag position is correct fix it by clicking <a href='/?op=builtin/config/ngrok&action=switchIfNeeded'>switch if need</a>
<br/>\n to only check click <a href='/?op=builtin/config/ngrok&action=getStoreBaseUrl'>getStoreBaseUrl</a>
";
            die;
        }
    }


}
