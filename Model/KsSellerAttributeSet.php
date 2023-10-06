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

/**
 * KsSellerAttributeSet Model Class for Filtering Seller Id
 */
class KsSellerAttributeSet implements \Magento\Framework\Data\OptionSourceInterface
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
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $ksCollectionFactory
     * @param  \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
     * @param \Magento\Catalog\Model\ResourceModel\Product $ksProduct
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $ksCollectionFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Magento\Catalog\Model\ResourceModel\Product $ksProduct
    ) {
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProduct = $ksProduct;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (null == $this->ksOptions) {
            // Get Attribute Set Id
            $ksSellerAttribute = $this->ksSellerAttributeSet();
            $this->ksOptions = $this->ksCollectionFactory->create()
                ->setEntityTypeFilter($this->ksProduct->getTypeId())
                ->addFieldToFilter('attribute_set_id', ['in' => $ksSellerAttribute])
                ->toOptionArray();
        }

        return $this->ksOptions;
    }

    /**
     * Get Seller and Default Attribute Set Id
     * @return array $ksksArray
     */
    public function ksSellerAttributeSet()
    {
        // Get Seller id
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        // Create Collection
        $ksCollection = $this->ksCollectionFactory->create();
        // Array for storing value of attribute
        $ksArray = [];
        // Filter Collection
        $ksSellerAttributeArray = $ksCollection->setEntityTypeFilter($this->ksProduct->getTypeId())
            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        // Iterate collection
        foreach ($ksSellerAttributeArray as $ksValue) {
            $ksArray[] = $ksValue->getAttributeSetId();
        }
        // Get Default array
        $ksDefaultArray = $this->ksDataHelper->getKsDefaultAttributes();
        if (!empty($ksDefaultArray)) {
            foreach ($ksDefaultArray as $ksValue) {
                $ksArray[] = $ksValue;
            }
        }
        return $ksArray;
    }
}
