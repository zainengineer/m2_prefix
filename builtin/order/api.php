<?php
$initialClasses = get_declared_classes();

$magentoInc->setRestApiArea();

Class OrderApi
{
    function __construct(
        protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        protected \MagePrefix\ZInclude\OtherSnippets\ZOrderView $zOrderView,
    )
    {

        $this->orderRepository = $orderRepository;
    }

    public function showOrder(int $orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $orderView = $this->zOrderView->getOrder($order);
        return $orderView;
    }
}

;
/** @var \OrderApi $instanceName */
\ZActionDetect::showOutput(end($initialClasses), $magentoInc);
