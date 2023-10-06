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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\ProductAttribute;

use Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler;

/**
 * Override the ConfigurableAttributeHandler Class of Magento Default
 */
class KsConfigurableAttributes extends ConfigurableAttributeHandler
{
    /**
     * Attribute collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeColFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $ksCollectionFactory
    ) {
        parent::__construct($ksCollectionFactory);
    }
    /**
     * Override the method to remove seller's attribute
     * Retrieve list of attributes applicable for configurable product
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getApplicableAttributes()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection */
        $collection = $this->collectionFactory->create();
        return $collection->addFieldToFilter(
            'frontend_input',
            'select'
        )->addFieldToFilter(
            'is_user_defined',
            1
        )->addFieldToFilter(
            'is_global',
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
        )->addFieldToFilter(
            'ks_seller_id',
            0
        );
    }
}
