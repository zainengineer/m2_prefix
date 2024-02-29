<?php

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use MagePrefix\ZInclude\Traits\SingletonTrait;

Class AutoInclude
{
    use SingletonTrait;
    protected $programmaticSnippetPath;
    public function __construct(
        protected \MagePrefix\ZInclude\Links\ShowLinks $showLinks,
    )
    {
    }

    protected function start()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        require_once __DIR__ . "/lib/fatal_inc.php";
        $this->includeKnit();
    }

    protected function getSnippetName()
    {
        return $this->programmaticSnippetPath ?: $_GET['op'];
    }

    protected function getSnippetPath()
    {
        $vSnippetName = $this->getSnippetName();
        return __DIR__ . "/snippets/{$vSnippetName}.php";
    }

    protected function getSnippetNameIfCustomMissing()
    {
        $path = $this->getSnippetPath();
        return file_exists($path) ? false : $this->getSnippetName();
    }

    protected function includeKnit()
    {
        $vKnitPath = __DIR__ . "/lib/kint_inc.php";
        require_once $vKnitPath;
    }

    protected function addPreSnippet()
    {
        $vSnippetName = $this->getSnippetNameIfCustomMissing();
        if (!$vSnippetName) {
            return false;
        }

        $vCorePreSnippet = __DIR__ . "/snippets_core_pre/{$vSnippetName}.php";
        if (file_exists($vCorePreSnippet)) {
            $this->includeKnit();
            \MagePrefix\ZInclude\Snippets\Links::getSingleton()->showAllTypsOfSnippets();
            exit;
        }
    }

    protected function addSnippet()
    {
        $this->addPreSnippet();
    }

    protected function checkSnippet()
    {
        $path = $this->getSnippetPath();
        if (!file_exists($path)) {
            $this->includeKnit();
            !d("$path does not exist");
            !d("attempting  /?op=snippets");
//            throw new Exception(($path) . ' does not exist');
            $this->programmaticSnippetPath = 'snippets';
            $this->addPreSnippet();
        }
    }

    protected function bootstrapInclude()
    {

        try {
            if ($realBotStrapPath = realpath(__DIR__ . '/../app/bootstrap.php')){
                require $realBotStrapPath;
            }
            else{
                require __DIR__ . '/../../app/bootstrap.php';
            }
        } catch (\Exception $e) {
            echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
            exit(1);
        }
    }

    protected function getAppParams()
    {
        $params = $_SERVER;
        $params[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS] = [
            DirectoryList::PUB         => [DirectoryList::URL_PATH => ''],
            DirectoryList::MEDIA       => [DirectoryList::URL_PATH => 'media'],
            DirectoryList::STATIC_VIEW => [DirectoryList::URL_PATH => 'static'],
            DirectoryList::UPLOAD      => [DirectoryList::URL_PATH => 'media/upload'],
        ];
        return $params;
    }

    protected function includeMagento($params)
    {
        $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
        /** @var \Magento\Framework\App\Http $app */
//$app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
        /** @var TestApp $app */
        require_once __DIR__ . '/test_app.php';
        $app = $bootstrap->createApplication('TestApp');
        require_once __DIR__ . "/lib/magento_inc.php";
    }

    protected function initializeMagento()
    {
        $this->bootstrapInclude();
        $this->includeMagento($this->getAppParams());
    }

    protected function getMagentoInc()
    {
        $this->includeKnit();
        $this->initializeMagento();
        return $GLOBALS['magentoInc'];
    }

    protected function addBuiltInSnippet()
    {
        $vSnippetName = $this->getSnippetNameIfCustomMissing();
        if (!$vSnippetName) {
            return false;
        }

        $vBuiltInSnippet = __DIR__ . "/{$vSnippetName}.php";
        if (file_exists($vBuiltInSnippet)) {
            $this->includeMagentoSnippet($vBuiltInSnippet);
            exit;
        }
    }

    public function processSnippet()
    {
        $this->start();
        $this->addSnippetsList();
        $this->addPreSnippet();
        $this->addBuiltInSnippet();
        $this->checkSnippet();
        $this->includeMagentoSnippet($this->getSnippetPath());
    }
    protected function addSnippetsList()
    {
        if ($this->getSnippetName() === 'snippets'){
             $this->showLinks->showAllTypsOfSnippets();
             d(1);die;
        }
    }

    protected function includeMagentoSnippet($path)
    {
        if (!isset($_GET['action'])){
            $GLOBALS['no_magento_inc'] = 1;
            $this->includeKnit();
            require_once __DIR__ . "/lib/magento_inc.php";
            require $path;
            return ;
        }
        $magentoInc = $this->getMagentoInc();
        $bExecuteNow = true;
        try{
            stream_wrapper_restore('phar');
            require $path;
        }catch(\Exception $e){
            xdebug_break();
            \zain_custom\lib\ErrorPrinting::showException($e);
            !d($e->getFile(). ':' . $e->getLine());
            throw $e;
        }
    }
}
