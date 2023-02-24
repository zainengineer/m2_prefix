<?php
$initialClasses = get_declared_classes();
/**
 * @var $magentoInc
 * @see \MagentoInc::setAdminHtml()
 */

$magentoInc->setAdminHtml();

class UnCancelOrder
{
    public function __construct()
    {
    }

    public function uncancel(\Magento\Sales\Model\Order $magentoOrder)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var $eventManager \Magento\Framework\Event\ManagerInterface
         * @var $stockIndexerProcessor \Magento\CatalogInventory\Model\Indexer\Stock\Processor
         * @var $stockManagement \Magento\CatalogInventory\Api\StockManagementInterface
         * @var $orderRepository \Magento\Sales\Api\OrderRepositoryInterface
         */
        $eventManager = $objectManager->get('\Magento\Framework\Event\ManagerInterface');
        $stockIndexerProcessor = $objectManager->get('\Magento\CatalogInventory\Model\Indexer\Stock\Processor');
        $stockManagement = $objectManager->get('\Magento\CatalogInventory\Api\StockManagementInterface');
        $orderRepository = $objectManager->get('\Magento\Sales\Api\OrderRepositoryInterface');
        $state = $objectManager->get('\Magento\Framework\App\State');

        $comment = "order uncancelled by etika manual code snippet";
        $orderId = 16;
        $incrementId = '2000000008';

        $state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $magentoOrder = $orderRepository->get($orderId);
        if ($magentoOrder->getIncrementId() != $incrementId) {
            throw new \Exception('order increment Id does not match');
        }
        if (!$magentoOrder->isCanceled()) {
            throw new \Exception('order is not cancelled');
        }
        if ($magentoOrder->getPayment()->getMethod() != 'etikapayment') {
            throw new \Exception('order is not etika');
        }


        $state = $magentoOrder::STATE_PROCESSING;
        $productStockQty = [];
        foreach ($magentoOrder->getAllVisibleItems() as $item) {
            $productStockQty[$item->getProductId()] = $item->getQtyCanceled();
            foreach ($item->getChildrenItems() as $child) {
                $productStockQty[$child->getProductId()] = $item->getQtyCanceled();
                $child->setQtyCanceled(0);
                $child->setTaxCanceled(0);
                $child->setDiscountTaxCompensationCanceled(0);
            }
            $item->setQtyCanceled(0);
            $item->setTaxCanceled(0);
            $item->setDiscountTaxCompensationCanceled(0);
        }

        $magentoOrder->setSubtotalCanceled(0);
        $magentoOrder->setBaseSubtotalCanceled(0);
        $magentoOrder->setTaxCanceled(0);
        $magentoOrder->setBaseTaxCanceled(0);
        $magentoOrder->setShippingCanceled(0);
        $magentoOrder->setBaseShippingCanceled(0);
        $magentoOrder->setDiscountCanceled(0);
        $magentoOrder->setBaseDiscountCanceled(0);
        $magentoOrder->setTotalCanceled(0);
        $magentoOrder->setBaseTotalCanceled(0);
        $magentoOrder->setState($state);
        $magentoOrder->setStatus($magentoOrder->getConfig()->getStateDefaultStatus($state));
        if (!empty($comment)) {
            $magentoOrder->addStatusHistoryComment($comment, false);
        }

        /* Reverting inventory */
        $itemsForReindex = $stockManagement->registerProductsSale(
            $productStockQty,
            $magentoOrder->getStore()->getWebsiteId()
        );
        $productIds = [];
        foreach ($itemsForReindex as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }
        if (!empty($productIds)) {
            $stockIndexerProcessor->reindexList($productIds);
        }
        $magentoOrder->setInventoryProcessed(true);

        $magentoOrder->save();

    }
}

\ZActionDetect::showOutput(end($initialClasses), $magentoInc);
