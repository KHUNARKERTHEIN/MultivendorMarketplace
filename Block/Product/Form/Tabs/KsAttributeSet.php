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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * KsAttributeSet block class
 */
class KsAttributeSet extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{
    /**
     * Set collection factory
     *
     * @var CollectionFactory
     * @since 101.0.0
     */
    protected $ksAttributeSetCollectionFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param CollectionFactory $ksAttributeSetCollectionFactory
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        CollectionFactory $ksAttributeSetCollectionFactory,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        KsSellerHelper $ksSellerHelper,
        DataPersistorInterface $ksDataPersistor,
        array $ksData = []
    ) {
        $this->ksAttributeSetCollectionFactory = $ksAttributeSetCollectionFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * Return category tree
     * @return json
     */
    public function getKsAttributeSetOption()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
        $ksCollection = $this->ksAttributeSetCollectionFactory->create();
        // Get Attribute Set Id
        $ksSellerAttribute = $this->ksSellerAttributeSet();
        $ksCollection->setEntityTypeFilter($this->getKsProduct()->getResource()->getTypeId())
            ->addFieldToFilter('attribute_set_id', ['in' => $ksSellerAttribute])
            ->addFieldToSelect('attribute_set_id', 'value')
            ->addFieldToSelect('attribute_set_name', 'label')
            ->setOrder(
                'attribute_set_name',
                \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::SORT_ORDER_ASC
            );

        $ksCollectionData = $ksCollection->getData() ?? [];
        // Get Seller Set Id
        $ksSellerSetId = $this->ksSellerSetId();

        foreach ($ksCollectionData as $ksKey => $ksRecord) {
            $ksValue = $ksRecord['value'];
            if (!in_array($ksValue, $ksSellerSetId)) {
                $ksCollectionData[$ksKey]['value'] = $ksRecord['value'];
                $ksCollectionData[$ksKey]['label'] = $ksRecord['label'].' [Admin]';
            }
        }

        return json_encode($ksCollectionData);
    }

    /**
     * Return category value
     * @return json
     */
    public function getKsAttributeValue()
    {
        return json_encode($this->getKsProduct()->getAttributeSetId());
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
        $ksCollection = $this->ksAttributeSetCollectionFactory->create();
        // Array for storing value of attribute
        $ksArray = [];
        // Filter Collection
        $ksSellerAttributeArray = $ksCollection->setEntityTypeFilter($this->getKsProduct()->getResource()->getTypeId())
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

    /**
     * Get Seller Set Id
     * @return array $ksksArray
     */
    public function ksSellerSetId()
    {
        // Get Seller id
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        // Create Collection
        $ksCollection = $this->ksAttributeSetCollectionFactory->create();
        // Array for storing value of attribute
        $ksArray = [];
        // Filter Collection
        $ksSellerAttributeArray = $ksCollection->setEntityTypeFilter($this->getKsProduct()->getResource()->getTypeId())
            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        // Iterate collection
        foreach ($ksSellerAttributeArray as $ksValue) {
            $ksArray[] = $ksValue->getAttributeSetId();
        }
        return $ksArray;
    }

    public function ksSellerAttributeSets()
    {
        // Get Seller id
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        // Create Collection
        $ksCollection = $this->ksAttributeSetCollectionFactory->create();
        // Array for storing value of attribute
        $ksArray = [];
        // Filter Collection
        $ksSellerAttributeArray = $ksCollection->setEntityTypeFilter($this->getKsProduct()->getResource()->getTypeId())
            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        // Iterate collection
        foreach ($ksSellerAttributeArray as $ksKey => $ksValue) {
            $ksArray[$ksKey]['label'] = $ksValue->getAttributeSetName();
            $ksArray[$ksKey]['value'] = $ksValue->getAttributeSetId();
        }
        return $ksArray;
    }
}
