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

namespace Ksolves\MultivendorMarketplace\Model\Source\ProductAttribute;

/**
 * Attribute Set Options
 */
class KsOptions implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var array
     */
    protected $ksOptions;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $ksProduct;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $ksProductFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $ksProduct
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $ksCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Magento\Catalog\Model\ResourceModel\Product $ksProduct
    ) {
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksProduct = $ksProduct;
        $this->ksProductFactory = $ksProductFactory;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (null == $this->ksOptions) {
            $ksDefault = $this->ksProductFactory->create()->getDefaultAttributeSetId();
            $this->ksOptions = $this->ksCollectionFactory->create()
                ->setEntityTypeFilter($this->ksProduct->getTypeId())->addFieldToFilter('ks_seller_id', 0)->addFieldToFilter('attribute_set_id', ['neq' => $ksDefault])
                ->toOptionArray();
        }

        return $this->ksOptions;
    }
}
