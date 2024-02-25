<?php

$magentoInc->setAdminHtml();

Class baseUrl{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }
    public function showValue()
    {
        return $this->scopeConfig->getValue('web/unsecure/base_url');
    }
};
/** @var \zain_custom\mun_snippet\baseUrl $baseUrl */
$baseUrl = $magentoInc->getObjectFromName('\baseUrl');
d($baseUrl->showValue());

/**
 * Alternative approach without DI
 */

/** @var \Magento\Framework\App\Config\ScopeConfigInterface $configAnotherWay */
$configAnotherWay = getObjectFromName('\Magento\Framework\App\Config\ScopeConfigInterface');
d($configAnotherWay->getValue('web/unsecure/base_url'));
