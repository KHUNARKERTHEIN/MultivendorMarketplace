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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\AssignProduct;

/**
 * Attribute Set Options
 */
class KsAttributeSetOptions extends \Magento\Catalog\Model\Product\AttributeSet\Options
{
    /**
     * To Show Options of Admin Attribute Set
     * @return array
     */
    public function toOptionArray()
    {
        if (null == $this->options) {
            $this->options = $this->collectionFactory->create()
                ->setEntityTypeFilter($this->product->getTypeId())
                ->addFieldToFilter('ks_seller_id', 0)
                ->toOptionArray();
        }

        return $this->options;
    }
}
