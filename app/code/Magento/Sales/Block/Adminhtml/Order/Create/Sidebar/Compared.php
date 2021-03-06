<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

/**
 * Adminhtml sales order create sidebar compared block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Compared extends \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_sidebar_compared');
        $this->setDataId('compared');
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Products in Comparison List');
    }

    /**
     * Retrieve item collection
     *
     * @return mixed
     */
    public function getItemCollection()
    {
        $collection = $this->getData('item_collection');
        if (is_null($collection)) {
            if ($collection = $this->getCreateOrderModel()->getCustomerCompareList()) {
                $collection = $collection->getItemCollection()->useProductItem(
                    true
                )->setStoreId(
                    $this->getQuote()->getStoreId()
                )->addStoreFilter(
                    $this->getQuote()->getStoreId()
                )->setCustomerId(
                    $this->getCustomerId()
                )->addAttributeToSelect(
                    'name'
                )->addAttributeToSelect(
                    'price'
                )->addAttributeToSelect(
                    'image'
                )->addAttributeToSelect(
                    'status'
                )->load();
            }
            $this->setData('item_collection', $collection);
        }
        return $collection;
    }

    /**
     * Get item id
     *
     * @param \Magento\Framework\Object $item
     * @return int
     */
    public function getItemId($item)
    {
        return $item->getCatalogCompareItemId();
    }
}
