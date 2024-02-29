<?php

namespace MagePrefix\ZInclude\OtherSnippets;

use MagePrefix\ZInclude\Traits\SingletonTrait;

class ZOrderView
{
    use SingletonTrait;
    public function __construct(
        protected ZReflection $ZReflection,
    )
    {
    }

    public function getOrder(\Magento\Sales\Api\Data\OrderInterface $order) : array
    {
        \KintHelper::setMaxDepth(4);
        return $this->ZReflection->recursiveData($order);
    }
}
