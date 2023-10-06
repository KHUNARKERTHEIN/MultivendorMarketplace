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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\ProductAttribute;

/**
 * KsHideSellerAttribute Class For Changing Removing Seller Product Attribute From Attribute Set
 */
class KsHideSellerAttribute extends \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main
{

    /**
     * Remove Seller Attribute from Retrieve Unused in Attribute Set Attribute Tree as JSON
     *
     * @return string
     */
    public function getAttributeTreeJson()
    {
        $ksItems = [];
        $ksSetId = $this->_getSetId();

        $ksCollection = $this->_collectionFactory->create()->setAttributeSetFilter($ksSetId)->load();

        $ksAttributesIds = ['0'];
        /* @var $item \Magento\Eav\Model\Entity\Attribute */
        foreach ($ksCollection->getItems() as $ksItem) {
            $ksAttributesIds[] = $ksItem->getAttributeId();
        }

        $ksAttributes = $this->_collectionFactory->create()->setAttributesExcludeFilter(
            $ksAttributesIds
        )->addVisibleFilter()->addFieldtoFilter('ks_seller_id', ['eq' => 0])->load();

        foreach ($ksAttributes as $child) {
            $ksAttr = [
                'text' => $this->escapeHtml($child->getAttributeCode()),
                'id' => $child->getAttributeId(),
                'cls' => 'leaf',
                'allowDrop' => false,
                'allowDrag' => true,
                'leaf' => true,
                'is_user_defined' => $child->getIsUserDefined(),
                'entity_id' => $child->getEntityId(),
            ];

            $ksItems[] = $ksAttr;
        }

        if (count($ksItems) == 0) {
            $ksItems[] = [
                'text' => __('Empty'),
                'id' => 'empty',
                'cls' => 'folder',
                'allowDrop' => false,
                'allowDrag' => false,
            ];
        }
        return $this->_jsonEncoder->encode($ksItems);
    }
}
