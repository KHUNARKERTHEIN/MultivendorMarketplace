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
namespace Ksolves\MultivendorMarketplace\Block\PriceComparison;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * KsPriceComparisonProductTabs Block Class
 */
class KsPriceComparisonProductTabs extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsProductTabs
{
    /**
     * Loads attributes for specified groups
     *
     * @param AttributeGroupInterface[] $groups
     * @return ProductAttributeInterface[]
     */
    public function getKsAttributes($ksGroup)
    {
        $ksAttributes = [];

        $ksSortOrder = $this->ksSortOrderBuilder
            ->setField('sort_order')
            ->setAscendingDirection()
            ->create();

        $ksSearchCriteria = $this->ksSearchCriteriaBuilder
            ->addFilter(AttributeGroupInterface::GROUP_ID, $ksGroup->getAttributeGroupId())
            ->addFilter(ProductAttributeInterface::IS_VISIBLE, 1)
            ->addSortOrder($ksSortOrder)
            ->create();

        $ksGroupAttributes = $this->ksAttributeRepository->getList($ksSearchCriteria)->getItems();

        $ksProductType = $this->getKsProduct()->getTypeId();
        $ksPriceComparisonProductField = ['status','sku','price','quantity_and_stock_status','weight_type','tax_class_id','description','url_key','meta_title','meta_keyword','meta_description'];
        $ksWeight = array('weight');
        if ($ksProductType == "simple" || $ksProductType == "configurable") {
            $ksPriceComparisonProductField = array_merge($ksPriceComparisonProductField, $ksWeight);
        }
        foreach ($ksGroupAttributes as $ksAttribute) {
            $ksApplyTo = $ksAttribute->getApplyTo();
            $ksIsRelated = !$ksApplyTo || in_array($ksProductType, $ksApplyTo);
            if (!in_array($ksAttribute->getAttributeCode(), $ksPriceComparisonProductField)) {
                continue;
            }
            if ($ksIsRelated) {
                $ksAttributes[] = $ksAttribute;
            }
        }

        return $ksAttributes;
    }

    public function getKsProductTabs()
    {
        if ($this->getRequest()->getParam('parent_id') && $this->getKsProduct()->getTypeId() != "configurable") {
            $this->removeKsTab('ks-product-configurable');
        }

        if ($this->getRequest()->getParam('parent_id') && $this->getKsProduct()->getTypeId() != "downloadable") {
            $this->removeKsTab('downloadable-product');
        }

        return  parent::getKsProductTabs();
    }
}
