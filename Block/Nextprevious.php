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
    protected $_objectManager;
    
    /**
     * @var Product
     */
    protected $_product;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    protected $_helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager,
     * @param array $data
     */

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magepow\Nextprevious\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry  = $context->getRegistry();
        $this->_objectManager = $objectManager;
        $this->_helper        = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    public function getModel($model) {
        return $this->_objectManager->create($model);
    }

    public function getCategoryProductIds($current_category) {
        $category_products = $current_category->getProductCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', array('eq' => 1))
            ->addAttributeToFilter('visibility', array('eq' => 4))
            ->addAttributeToSort('position', 'asc');
            
        $cat_prod_ids = $category_products->getAllIds();
        
        return $cat_prod_ids;
    }

    public function getCurrentCategory($product){
        $current_category = $product->getCategory();
        if(!$current_category) {
            foreach($product->getCategoryCollection() as $parent_cat) {
                $current_category = $parent_cat;
            }
        }
        if(!$current_category)
            return false;
        return $current_category;
    }

    public function getPrevProduct($product) {
        $current_category = $this->getCurrentCategory($product);

        if(!$current_category)
            return false;
        $cat_prod_ids = $this->getCategoryProductIds($current_category);
        $_pos = array_search($product->getId(), $cat_prod_ids);
        if($this->getConfigModule('general/sort')){
            if (isset($cat_prod_ids[$_pos - 1])) {
                $prev_product = $this->getModel('Magento\Catalog\Model\Product')->load($cat_prod_ids[$_pos - 1]);
                return $prev_product;
            }
        }else{

            if (isset($cat_prod_ids[$_pos + 1])) {
                $prev_product = $this->getModel('Magento\Catalog\Model\Product')->load($cat_prod_ids[$_pos + 1]);
                return $prev_product;
            }
        }
        return false;
    }
    
    public function getNextProduct($product) {
        $current_category = $this->getCurrentCategory($product);
        
        if(!$current_category)
            return false;
        $cat_prod_ids = $this->getCategoryProductIds($current_category);
        $_pos = array_search($product->getId(), $cat_prod_ids);
        if($this->getConfigModule('general/sort')){
            if (isset($cat_prod_ids[$_pos + 1])) {
                $next_product = $this->getModel('Magento\Catalog\Model\Product')->load($cat_prod_ids[$_pos + 1]);
                return $next_product;
            }
        }else{

            if (isset($cat_prod_ids[$_pos - 1])) {
                $next_product = $this->getModel('Magento\Catalog\Model\Product')->load($cat_prod_ids[$_pos - 1]);
                return $next_product;
            }
        }
        return false;
    }

}
