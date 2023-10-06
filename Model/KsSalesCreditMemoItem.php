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
 * KsSalesCreditMemoItem Model Class
 */
class KsSalesCreditMemoItem extends AbstractModel
{
    
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesCreditMemoItem');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_sales_creditmemo_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_sales_creditmemo_item';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_sales_creditmemo_item';

    /**
     * Set Credit Memo Details
     *
     * @param Object $ksCreditMemoItem
     * @param Int    $ksParentId
     */
    public function setKsCreditMemoItem($ksCreditMemoItem,$ksParentId,$ksFormItem=[])
    {
        $ksData =[
            'ks_parent_id' => $ksParentId,
            'ks_base_price' => $ksCreditMemoItem->getBasePrice(),
            'ks_tax_amount' => $ksCreditMemoItem->getTaxAmount(),
            'ks_base_row_total' => $ksCreditMemoItem->getBaseRowTotal(),
            'ks_discount_amount' => $ksCreditMemoItem->getDiscountAmount(),
            'ks_row_total' => $ksCreditMemoItem->getRowTotal(),
            'ks_base_discount_amount' => $ksCreditMemoItem->getBaseDiscountAmount(),
            'ks_price_incl_tax' => $ksCreditMemoItem->getPriceInclTax(),
            'ks_base_tax_amount' => $ksCreditMemoItem->getBaseTaxAmount(),
            'ks_base_price_incl_tax' => $ksCreditMemoItem->getBasePriceInclTax(),
            'ks_qty' => $ksCreditMemoItem->getQty(),
            'ks_back_to_stock' => isset($ksFormItem['back_to_stock']),
            'ks_base_cost' => $ksCreditMemoItem->getBaseCost(),
            'ks_price' => $ksCreditMemoItem->getPrice(),
            'ks_base_row_total_incl_tax' => $ksCreditMemoItem->getBaseRowTotalInclTax(),
            'ks_row_total_incl_tax' => $ksCreditMemoItem->getRowTotalInclTax(),
            'ks_product_id' => $ksCreditMemoItem->getProductId(),
            'ks_order_item_id' => $ksCreditMemoItem->getOrderItemId(),
            'ks_additional_data' => $ksCreditMemoItem->getAdditionalData(),
            'ks_description' => $ksCreditMemoItem->getDescription(),
            'ks_sku' => $ksCreditMemoItem->getSku(),
            'ks_name' => $ksCreditMemoItem->getName(),
            'ks_discount_tax_compensation_amount' => $ksCreditMemoItem->getDiscountTaxCompensationAmount(),
            'ks_base_discount_tax_compensation_amount' => $ksCreditMemoItem->getBaseDiscountTaxCompensationAmount(),
            'ks_tax_ratio' => $ksCreditMemoItem->getTaxRatio()
        ];
        $this->setData($ksData)->save();
    }
}
