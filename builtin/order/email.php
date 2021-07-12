<?php
$initialClasses = get_declared_classes();

$magentoInc->setAdminHtml();

class OrderEmail
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;
    /**
     * @var \Magento\Sales\Model\OrderNotifier
     */
    private $orderNotifier;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\OrderNotifier $orderNotifier
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderNotifier = $orderNotifier;
    }

    public function sendViaRawCode()
    {
        $orderIncrementId = '000000001';
        $orderIncrementId = '000000009';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $state = $objectManager->get('\Magento\Framework\App\State');
        $state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderIncrementId);
        $objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
    }

    public function orderEmail()
    {
//        $orderIncrementId = '000000001';
        $orderIncrementId = '000000009';
//        $orderIncrementId = '000000019';
//        $orderIncrementId = '000000019';
        $order = $this->orderFactory->create();
        $order = $order->loadByIncrementId($orderIncrementId);
        return $this->orderNotifier->notify($order);
    }

    public function test()
    {
        return $this->orderEmail();
//        return $this->zendEmail();
    }
}

ZActionDetect::showOutput(end($initialClasses), $magentoInc);
