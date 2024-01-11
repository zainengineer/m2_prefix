<?php

use Magento\Framework\App\Config\ScopeConfigInterface;

$initialClasses = get_declared_classes();

$magentoInc->setAdminHtml();

class NgrokConfig
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $config;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $directoryList;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $typeList;
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $cachePool;
    private \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollectionFactory;

    public function __construct(
        \Magento\Config\Model\ResourceModel\Config                       $config,
        \Magento\Framework\Filesystem\DirectoryList                      $directoryList,
        \Magento\Framework\App\Cache\TypeListInterface                   $typeList,
        Magento\Framework\App\Cache\Frontend\Pool                        $cachePool,
        Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface                       $storeManager
    )
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->typeList = $typeList;
        $this->cachePool = $cachePool;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    public function getStoreBaseUrl()
    {
        $url = $this->getCurrentStoreBaseUrl();
        if (!strpos($url, 'ngrok')) {
            $this->saveOriginalUrlInConfig($url);
        }
        return $url;
    }
    protected function saveOriginalUrlInConfig(string $url)
    {
        $this->addUpdateConfig('original_base_url', $url);
    }
    protected function getNgrokCurrentUrl()
    {
        $ngrokDomain = getallheaders()['X-Forwarded-Host'];
        if (strpos($ngrokDomain,'ngrok')){
            return "https://$ngrokDomain/";
        }
        return false;
    }
    public function getMatchingUrls()
    {
        return array_map(function (\Magento\Framework\App\Config\Value $config) {
            return $config->getData();
        }, $this->getMatchingUrlsItems());
    }
    protected function saveConfig(array $configsToSave)
    {
        $restored = [];
        foreach ($configsToSave as $singleConfig) {
            $this->config->saveConfig($singleConfig['path'], $singleConfig['value'],$singleConfig['scope'],$singleConfig['scope_id']);
            $restored[] = $singleConfig['value'];
        }
        $this->cleanConfigCache();
        return $restored;
    }
    public function restoreOriginalUrls()
    {
        $config = $this->getFlagConfig();
        $originalConfigs =  $config['original_configs'];
        if (!is_array($originalConfigs)){
            throw new \Exception('$originalConfigs is not an array');
        }
        return $this->saveConfig($originalConfigs);
    }
    protected function switchToNgrok()
    {
        xdebug_break();
        \MagePrefix\ZInclude\ZInc::dInc(3);
        $configData = $this->getMatchingUrls();
        $originalConfigs = array_filter($configData, function ($item) {
            return !strpos($item['value'], 'ngrok');
        });
        $originalConfigs && $this->addUpdateConfig('original_configs', $originalConfigs);
        if (!($ngrokUrl = $this->getNgrokCurrentUrl())){
            throw new \Exception("could not get current ngrok url");
        }

        $ngrokConfig = array_map(function($item) use ($ngrokUrl){
            $url = $item['value'];
            $parts = parse_url($url);
            $item['value'] = rtrim($ngrokUrl,'/') . $parts['path'];
            return $item;
        },$originalConfigs);
        $this->saveConfig($ngrokConfig);
        return $ngrokUrl;
    }
    protected function getFlagConfig() : array
    {
        $flagPath = \MagePrefix\ZInclude\ZNgrock::getFlagPath();
        $data = @json_decode(@file_get_contents($flagPath), true);
        return $data ?: [];
    }
    protected function addUpdateConfig(
        string $key,
               $value,)
    {
        $flagPath = \MagePrefix\ZInclude\ZNgrock::getFlagPath();
        $data = $this->getFlagConfig();
        $data[$key] = $value;
        file_put_contents($flagPath, json_encode($data, JSON_PRETTY_PRINT));
    }

    protected function getCurrentStoreBaseUrl()
    {
        if (empty($_SERVER['MAGE_RUN_CODE'])) {
            throw new \Exception("MAGE_RUN_CODE not available");
        }
        if (empty($_SERVER['MAGE_RUN_TYPE'])) {
            throw new \Exception("MAGE_RUN_TYPE not available available");
        }
        $runCode = $_SERVER['MAGE_RUN_CODE'];
        $runType = $_SERVER['MAGE_RUN_TYPE'];
        if ($runType == 'store') {
            $store = $this->storeManager->getStore($runCode);
            return $store->getBaseUrl();
        }
        if ($runType == 'website') {
            $website = $this->storeManager->getWebsite($runCode);
            !d($website);
            throw new \Exception("implement code ");
            return $website->getStore()->getBaseUrl();
        }

        throw new \Exception("Unexpected RunType $runType");

    }

    protected function getUrlConfigsCollection()
    {
        return $this->configCollectionFactory->create()->addFieldToFilter('path', [
            'like' => 'web/%secure/base%_url',
        ]);
    }

    protected function getAllConfigUrls()
    {
        \MagePrefix\ZInclude\ZInc::dInc(3);
        return array_map(function (\Magento\Framework\App\Config\Value $item) {
            return $item->getValue();
        }, $this->getMatchingUrlsItems());
//        $items = $this->getUrlConfigsCollection()->getItems();
        xdebug_break();
        \MagePrefix\ZInclude\ZInc::dInc(3);
        return $items;
    }

    protected function cleanConfigCache()
    {
        $this->typeList->cleanType('config');
        foreach ($this->cachePool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, \Magento\Config\App\Config\Type\System::CACHE_TAG);
        }
    }
    protected function setBaseUrls(string $url,
                                   bool   $cleanCache,
                                          $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        if ($url){
            $this->config->saveConfig('web/unsecure/base_url', $url, $scope, $scopeId);
            $this->config->saveConfig('web/secure/base_url', $url, $scope, $scopeId);
        }
        if ($cleanCache){
            $this->cleanConfigCache();;
        }
    }

    protected function getMatchingUrlsItems()
    {
        xdebug_break();
        //not same as $_SERVER['HTTP_HOST'] if valet is being used
        $host = getallheaders()['Host'];
        if (strpos($host, 'ngrok')) {
            throw new \Exception('fix host header detection');
        }
        return array_filter($this->getUrlConfigsCollection()->getItems(), function ($item) use ($host) {
            if (!($item instanceof \Magento\Framework\App\Config\Value)) {
                !d($item);
                throw new \Exception("item is not of correct type");
            }
            $url = $item->getValue();
            if (!$url){
                return false;
            }
            return strpos($url, $host);
        });
    }

    protected function isNgrok() : bool
    {
        $forwardHost = getallheaders()['X-Forwarded-Host']??'';
        if ($forwardHost
            && strpos($forwardHost,'ngrok')){
            return  true;
        }
        return false;
    }
    public function switchIfNeeded()
    {
        if ($this->isNgrok()){
           return  $this->switchToNgrok();
        }
        return $this->restoreOriginalUrls();
    }
    public function switchToNgrokIfAvailable()
    {

        if ($this->isNgrok()){
            return  $this->switchToNgrok();
        }
        return "not an ngrok domain";
    }
}

\ZActionDetect::showOutput(end($initialClasses), $magentoInc);
