<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\Shipment\View;

/**
 * Adminhtml sales item renderer
 */
class Items extends \Ksolves\MultivendorMarketplace\Block\Adminhtml\Items\AbstractItems
{
    /**
     * Retrieve shipment order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->ksRegistry->registry('current_order');
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getSource()
    {
        return $this->getShipment();
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        if ($this->ksRegistry->registry('current_shipment')) {
            return $this->ksRegistry->registry('current_shipment');
        } else {
            return $this->ksRegistry->registry('current_shipment_request');
        }
    }

    /**
     * Retrieve shipment items model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment\Items
     */
    public function getShipmentItems()
    {
        return $this->ksRegistry->registry('current_shipment_items');
    }

    /**
     * @return bool
     */
    public function canSendCommentEmail()
    {
        return $this->ksSalesData->canSendShipmentCommentEmail(
            $this->getOrder()->getStoreId()
        );
    }

    /**
     * Retrieve order url
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getShipment()->getOrderId()]);
    }

    /**
     * Retrieve formatted price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * Get comment collection for the shipment
     *
     * @return Object
     */
    public function getKsComments()
    {
        if ($this->getShipment()->getKsApprovalStatus()) {
            $ksEntityId = $this->ksOrderHelper->getShipmentId($this->getShipment()->getEntityId());
            return $this->ksShipmentCommentFactory->create()->addFieldToFilter('parent_id', $ksEntityId)->addAttributeToSort('entity_id', 'desc');
        } else {
            return $this->getShipment();
        }
    }

    /**
     * Returns the data from  Sales Order Item table
     *
     * @param \Magento\Sales\Model\Order\Item $ksSalesOrderItem
     * @return  string
     */
    public function getKsOrderItem($ksItemId)
    {
        return $this->ksSalesOrderItem->load($ksItemId);
    }

    /**
     * Returns the data for particular product
     *
     * @param \Magento\Sales\Helper\Data
     * @return  string
     */
    public function getKsShipmentItemAttrValue($ksItemId)
    {
        $attrvalue = $this->getKsOrderItem($ksItemId);
        return $this->ksOrderHelper->getKsShipmentItemAttrValue($attrvalue);
    }

    /**
     * @return \Magento\Sales\Order\Shipment\Item
     */
    public function getKsSalesShipmentItem($ksShipmentIncrId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesShipmentItem($ksShipmentIncrId, $ksOrderItemId);
    }

    /**
     * @return \Ksolves\MultivendorMarketplace\Model\KsSalesCreditmemoItem
     */
    public function getKsSalesShipmentReqItem($ksReqId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesShipmentReqItem($ksReqId, $ksOrderItemId);
    }
}
