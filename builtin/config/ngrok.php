<?php
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

    public function __construct(
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Cache\TypeListInterface $typeList,
        Magento\Framework\App\Cache\Frontend\Pool $cachePool,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->typeList = $typeList;
        $this->cachePool = $cachePool;
    }

    public function getStoreBaseUrl(): string
    {
        $store = $this->storeManager->getStore(0);
        $baseUrl = '';
        if ($store instanceof \Magento\Store\Model\Store) {
            $baseUrl = $store->getBaseUrl();
        }
        return $baseUrl;
    }

    protected function setBaseUrls($url)
    {
        $this->config->saveConfig('web/unsecure/base_url', $url);
        $this->config->saveConfig('web/secure/base_url', $url);
        $this->typeList->cleanType('config');
        foreach ($this->cachePool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, \Magento\Config\App\Config\Type\System::CACHE_TAG);
        }
    }

    public function switchIfNeeded()
    {
        $flagPath = $this->directoryList->getRoot() . '/pre_ngrok_base_url.flag';
        $flagExists = file_exists($flagPath);
        $httpHost = $_SERVER['HTTP_HOST'];
        $ngrokInDomain = strpos($httpHost, '.ngrok.io') ? true : false;

        if (!$flagExists && !$ngrokInDomain) {
            return 'no action';
        }
        if ($flagExists && $ngrokInDomain) {
            return 'no action';
        }
        if ($ngrokInDomain && !$flagExists) {
            $baseUrl = $this->getStoreBaseUrl();
            file_put_contents($flagPath, $baseUrl);
            $newUrl = "http://{$httpHost}/";
            $this->setBaseUrls($newUrl);
            return "put to flag:  $baseUrl changed base url to $newUrl";
        }
        if ($flagExists && !$ngrokInDomain) {
            $originalUrl = file_get_contents($flagPath);
            $storeUrl = $this->getStoreBaseUrl();
            unlink($flagPath);
            $this->setBaseUrls($originalUrl);
            return "flag removed, base url changed from $storeUrl to $originalUrl";
        }
    }
}

\ZActionDetect::showOutput(end($initialClasses), $magentoInc);
