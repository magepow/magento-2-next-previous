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
     * @var array
     */
    
    protected $_nextPrevious;

    /**
     * @var \Magepow\Nextprevious\Helper\Data
     */
    public $_helper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magepow\Nextprevious\Helper\Data $helper
     * @param array $data
     */

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magepow\Nextprevious\Helper\Data $helper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper        = $helper;
        parent::__construct($context, $data);
    }

    public function getModel($model) 
    {
        return $this->_objectManager->create($model);
    }

    public function getCategoryProductIds($category) 
    {
        $categoryProducts = $category->getProductCollection();
        $productsPosition = $categoryProducts->getAllIds();
        // $productsPosition = $category->getProductsPosition();
        
        return $productsPosition;
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

    public function getNextPrevious($product)
    {
        if(!$this->_nextPrevious){
            $currentCategory = $this->getCurrentCategory($product);

            if(!$currentCategory) return;
            $productIds = $this->getCategoryProductIds($currentCategory);
            $_pos = array_search($product->getId(), $productIds);
            if($this->_helper->getConfigModule('general/sort')){
                $this->_nextPrevious['next'] = isset($productIds[$_pos + 1]) ? $productIds[$_pos + 1] : '';
                $this->_nextPrevious['prev'] = isset($productIds[$_pos - 1]) ? $productIds[$_pos - 1] : '';
            }else{
                $this->_nextPrevious['next'] = isset($productIds[$_pos - 1]) ? $productIds[$_pos - 1] : '';
                $this->_nextPrevious['prev'] = isset($productIds[$_pos + 1]) ? $productIds[$_pos + 1] : '';
            }
        }
        return $this->_nextPrevious;
    }

    public function getPrevProduct($product) 
    {
        $nextPrevious = $this->getNextPrevious($product);
        if(!isset($nextPrevious['prev']) || !$nextPrevious['prev']) return;
        $productId = $nextPrevious['prev'];
        return $this->getModel('Magento\Catalog\Model\Product')->load($productId);
    }
    
    public function getNextProduct($product)
    {
        $nextPrevious = $this->getNextPrevious($product);
        if(!isset($nextPrevious['next']) || !$nextPrevious['next']) return;
        $productId = $nextPrevious['next'];
        return $this->getModel('Magento\Catalog\Model\Product')->load($productId);
    }

}
