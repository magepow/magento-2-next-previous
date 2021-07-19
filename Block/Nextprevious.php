<?php
/*
 * Magepow
 * @category: Magepow
 * @copyright: Copyright (c) 2014 Magepow (http://www.magepow.com/)
 * @license: http://www.magepow.com/license-agreement
 * @Author: DavidDuong
 * @@Create Date: Wednesday, December 4th 2019, 8:43:54 pm
 * @@Modified By: DavidDuong
 * @@Modify Date: Friday, December 6th 2019, 3:39:15 pm
 */

namespace Magepow\Nextprevious\Block;

class Nextprevious extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    
    protected $_objectManager;

    /**
     * @var Data
     */
    protected $urlHelper;

    /**
     * @var array
     */
    
    protected $_nextPrevious;

    /**
     * @var \Magepow\Nextprevious\Helper\Data
     */
    public $_helper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager,
     * @param array $data
     */

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magepow\Nextprevious\Helper\Data $helper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->urlHelper      = $urlHelper;
        $this->_helper        = $helper;
        parent::__construct($context, $data);
    }

    public function getModel($model) 
    {
        return $this->_objectManager->create($model);
    }

    public function getCategoryProductIds($category) 
    {
        $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');
        $this->_productCollection = $categoryProducts;
        foreach ($categoryProducts as $product) {
            $this->_nextPrevious[$product->getId()] = $product;
        }
        return $this->_nextPrevious;
    }

    public function getCurrentCategory($product)
    {
        $currentCategory = $product->getCategory();
        if(!$currentCategory || $currentCategory->getIsActive() == 0){
            foreach($product->getCategoryCollection() as $category) {
                $categoryId = $category->getId();
                $currentCategory = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
                if($currentCategory->getIsActive()){
                    return $currentCategory;
                }
            }
            return;
        }

        return $currentCategory;
    }

    public function getPreviousAndNext($product)
    {
        $previousAndNext = [];
        if(!$this->_nextPrevious){
            $currentCategory = $this->getCurrentCategory($product);
            if(!$currentCategory) return;
            $this->_nextPrevious = $this->getCategoryProductIds($currentCategory);
        }
        $productId = $product->getId();
        $nextPrevious = $this->_nextPrevious;
        if(!$nextPrevious) return;
        $prevProduct  = '';
        foreach ($nextPrevious as $id => $product) {
            if($id == $productId) break;
            $prevProduct = $product;
            next($nextPrevious);
        }
        $previousAndNext[] = $prevProduct;
        $previousAndNext[] = next($nextPrevious);
        if(!$this->_helper->getConfigModule('general/sort')) array_reverse($previousAndNext);
        return $previousAndNext;
    }

    public function getPrevProduct($product) 
    {
        $previousAndNext = $this->getPreviousAndNext($product);
        $product = $previousAndNext ? current($previousAndNext) : '';
        return $product;
    }
    
    public function getNextProduct($product)
    {
        $previousAndNext = $this->getPreviousAndNext($product);
        $product = $previousAndNext ? next($previousAndNext) : '';
        return $product;
    }

}
