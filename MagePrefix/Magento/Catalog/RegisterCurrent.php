<?php
namespace MagePrefix\Magento\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Registry;

class RegisterCurrent
{
    public function __construct(
        protected Registry $coreRegistry,
        protected CategoryRepositoryInterface $categoryRepository,
    )
    {
    }
    public function setCurrentCategory(
        int $categoryId,
        bool $graceFully
    )
    {
        $category = $this->categoryRepository->get($categoryId);
        $this->coreRegistry->register('current_category',$category,$graceFully);
    }
}
