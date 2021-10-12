<?php
/**
 * To replace
 *
 * ConfigView
 *
 */
$magentoInc->setAdminHtml();

class ConfigView
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    )
    {

        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
    }

    protected function getConnection(): \Magento\Framework\DB\Adapter\AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    protected function getAllRows(string $vSql)
    {
        return $aReturn = $this->getConnection()->fetchAssoc($vSql);
    }

    public function getDbConfig(string $path)
    {
        return $this->getAllRows("select * from core_config_data where path like '%$path%'");
    }

    public function getDefaultConfig(string $path)
    {
        return $this->config->getValue($path);
    }
}

;
/** @var \ConfigView $instanceName */
$instanceName = $magentoInc->getObjectFromName('\ConfigView');

try {
    !d(ZActionDetect::callMethod($instanceName));
} catch (\ShowExceptionAsNormalMessage $e) {
    $message = $e->errorData?:$e->getMessage();
    if ($e->rawMessage){
        echo $e->rawMessage;
    }
    !d($message);
}