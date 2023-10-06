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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\CreditMemo\View;

/**
 * Adminhtml sales item renderer
 */
class Items extends \Ksolves\MultivendorMarketplace\Block\Adminhtml\Items\AbstractItems
{
    /**
     * Retrieve invoice order
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
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getSource()
    {
        return $this->getCreditMemo();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     */
    public function getOrderTotalData()
    {
        return ['grand_total_title' => __('Total Refund')];
    }
    
    /**
     * Retrieve credit memo model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getCreditMemo()
    {
        if ($this->ksRegistry->registry('current_creditmemo')) {
            return $this->ksRegistry->registry('current_creditmemo');
        } else {
            return $this->ksRegistry->registry('current_creditmemo_request');
        }
    }

    /**
     * Retrieve credit memo items model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getCreditMemoItems()
    {
        return $this->ksRegistry->registry('current_creditmemo_items');
    }

    /**
     * @return bool
     */
    public function canSendCommentEmail()
    {
        return $this->ksSalesData->canSendCreditMemoCommentEmail(
            $this->getOrder()->getStoreId()
        );
    }

    /**
     * Format total value based on base currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatValue($price, $precision = 2)
    {
        return $this->getOrder()->getBaseCurrency()->formatPrecision($price, $precision);
    }

    /**
     * Format total value based on order currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatToOrderCurrency($price)
    {
        return $this->getOrder()->formatPrice($price, true);
    }

    /**
     * Format total value based on order currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function canKsShowOrderCurrencyValue()
    {
        return !($this->getOrder()->getOrderCurrencyCode() == $this->getOrder()->getStoreCurrencyCode());
    }

    /**
     * Retrieve order url
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getCreditMemo()->getOrderId()]);
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
        if ($this->getCreditMemo()->getKsApprovalStatus()) {
            $ksEntityId = $this->ksOrderHelper->getCreditMemoId($this->getCreditMemo()->getEntityId());
            return $this->ksMemoCommentFactory->create()->addFieldToFilter('parent_id', $ksEntityId)->addAttributeToSort('entity_id', 'desc');
        } else {
            return $this->getCreditMemo();
        }
    }

    /**
     * Returns the data for particular product
     *
     * @param \Magento\Sales\Helper\Data
     * @return  string
     */
    public function getKsCreditMemoItemAttrValue($ksItemId)
    {
        $attrvalue = $this->getKsOrderItem($ksItemId);
        return $this->ksOrderHelper->getKsCreditMemoItemAttrValue($attrvalue);
    }

    /**
     * @return \Magento\Sales\Order\Creditmemo\Item
     */
    public function getKsSalesMemoItem($ksMemoIncrId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesMemoItem($ksMemoIncrId, $ksOrderItemId);
    }

    /**
     * @return \Ksolves\MultivendorMarketplace\Model\KsSalesCreditmemoItem
     */
    public function getKsSalesMemoReqItem($ksReqId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesMemoReqItem($ksReqId, $ksOrderItemId);
    }
}
