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

namespace Ksolves\MultivendorMarketplace\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * KsSalesInvoiceItem Model Class
 */
class KsSalesInvoiceItem extends AbstractModel
{

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesInvoiceItem');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_sales_invoice_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_sales_invoice_item';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_sales_invoice_item';

    /**
     * Set Order Details
     *
     * @param Object $ksOrder
     * @param Int    $ksSellerId
     */
    public function setKsInvoiceItem($ksInvoiceItem, $ksParentId)
    {
        $ksData =[
            'ks_parent_id' => $ksParentId,
            'ks_base_price' => $ksInvoiceItem->getBasePrice(),
            'ks_tax_amount' => $ksInvoiceItem->getTaxAmount(),
            'ks_base_row_total' => $ksInvoiceItem->getBaseRowTotal(),
            'ks_discount_amount' => $ksInvoiceItem->getDiscountAmount(),
            'ks_row_total' => $ksInvoiceItem->getRowTotal(),
            'ks_base_discount_amount' => $ksInvoiceItem->getBaseDiscountAmount(),
            'ks_price_incl_tax' => $ksInvoiceItem->getPriceInclTax(),
            'ks_base_tax_amount' => $ksInvoiceItem->getBaseTaxAmount(),
            'ks_base_price_incl_tax' => $ksInvoiceItem->getBasePriceInclTax(),
            'ks_qty' => $ksInvoiceItem->getQty(),
            'ks_base_cost' => $ksInvoiceItem->getBaseCost(),
            'ks_price' => $ksInvoiceItem->getPrice(),
            'ks_base_row_total_incl_tax' => $ksInvoiceItem->getBaseRowTotalInclTax(),
            'ks_row_total_incl_tax' => $ksInvoiceItem->getRowTotalInclTax(),
            'ks_product_id' => $ksInvoiceItem->getProductId(),
            'ks_order_item_id' => $ksInvoiceItem->getOrderItemId(),
            'ks_additional_data' => $ksInvoiceItem->getAdditionalData(),
            'ks_description' => $ksInvoiceItem->getDescription(),
            'ks_sku' => $ksInvoiceItem->getSku(),
            'ks_name' => $ksInvoiceItem->getName(),
            'ks_discount_tax_compensation_amount' => $ksInvoiceItem->getDiscountTaxCompensationAmount(),
            'ks_base_discount_tax_compensation_amount' => $ksInvoiceItem->getBaseDiscountTaxCompensationAmount(),
            'ks_tax_ratio' => $ksInvoiceItem->getTaxRatio()
        ];
        $this->setData($ksData)->save();
    }
}
