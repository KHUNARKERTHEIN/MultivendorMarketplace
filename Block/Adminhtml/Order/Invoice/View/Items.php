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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\Invoice\View;

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
        return $this->getInvoice();
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        if ($this->ksRegistry->registry('current_invoice')) {
            return $this->ksRegistry->registry('current_invoice');
        } else {
            return $this->ksRegistry->registry('current_invoice_request');
        }
    }

    /**
     * Retrieve invoice items model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice\Items
     */
    public function getInvoiceItems()
    {
        return $this->ksRegistry->registry('current_invoice_items');
    }

    /**
     * @return bool
     */
    public function canSendCommentEmail()
    {
        return $this->ksSalesData->canSendInvoiceCommentEmail(
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
        return $this->getUrl('sales/order/view', ['order_id' => $this->getInvoice()->getOrderId()]);
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
     * Get comment collection for the invoice
     *
     * @return Object
     */
    public function getKsComments()
    {
        if ($this->getInvoice()->getKsApprovalStatus()) {
            $ksEntityId = $this->ksOrderHelper->getInvoiceId($this->getInvoice()->getEntityId());
            return $this->ksCommentFactory->create()->addFieldToFilter('parent_id', $ksEntityId)->addAttributeToSort('entity_id', 'desc');
        } else {
            return $this->getInvoice();
        }
    }

    /**
     * @return \Magento\Sales\Order\Invoice\Item
     */
    public function getKsSalesInvoiceItem($ksInvoiceIncrId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesInvoiceItem($ksInvoiceIncrId, $ksOrderItemId);
    }

    /**
     * @return \Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceItem
     */
    public function getKsSalesInvoiceReqItem($ksInvoiceIncrId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesInvoiceReqItem($ksInvoiceIncrId, $ksOrderItemId);
    }
}
