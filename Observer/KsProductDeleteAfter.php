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

namespace Ksolves\MultivendorMarketplace\Observer;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as KsMainProductCollection;
use Magento\Framework\Event\ObserverInterface;

/**
 * KsProductDeleteAfter Observer class
 */
class KsProductDeleteAfter implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $ksProductCollection;

    /**
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var CollectionFactory
     */
    protected $ksMainProductCollection;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @param CollectionFactory $ksProductCollection
     * @param KsProductFactory $ksProductFactory
     * @param KsMainProductCollection $ksMainProductCollection
     * @param ProductRepositoryInterface $ksProductRepository
     * @param KsDataHelper $ksDataHelper
     */
    public function __construct(
        CollectionFactory $ksProductCollection,
        KsProductFactory $ksProductFactory,
        KsMainProductCollection $ksMainProductCollection,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        ProductRepositoryInterface $ksProductRepository
    ) {
        $this->ksProductCollection     = $ksProductCollection;
        $this->ksProductFactory        = $ksProductFactory;
        $this->ksMainProductCollection = $ksMainProductCollection;
        $this->ksDataHelper            = $ksDataHelper;
        $this->ksProductHelper         = $ksProductHelper;
        $this->ksProductRepository     = $ksProductRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $ksObserver)
    {
        // Get the Object of Deleted Product
        $ksDeletedProduct = $ksObserver->getEvent()->getProduct();
        // Get the Id of Deleted Product
        $ksDeletedProductId = $ksDeletedProduct->getEntityId();
        // Get the collection of product
        $ksProductCollection = $this->ksProductCollection->create()->addFieldtoFilter('ks_parent_product_id', $ksDeletedProductId);
        //Check the Product has price comparison product or not
        if ($ksProductCollection->getSize() == 1) {
            // If only one price comparison product is available then make it main product
            $ksProductCollection->getFirstItem()->setKsParentProductId(0)->save();

        // If Two Product Available Then Check allow the conditions
        } elseif ($ksProductCollection->getSize() > 1) {
            // Get the Product Id of Price Comparison Product
            $ksPriceComparisonProductId = $this->getKsPriceComparisonProductId($ksProductCollection);
            if ($ksDeletedProduct->getTypeId() == "configurable") {
                // Get the Condition of the System Configuration from the Backend
                $ksOwnerCondition = $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_secondary_configurable_product_owner');
                $this->ksProductHelper->ksSaveConfigProductAccordingToCondition($ksOwnerCondition);
            } else {
                // Get the Condition of the System Configuration from the Backend
                $ksOwnerCondition = $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_secondary_product_owner');
                $this->ksProductHelper->ksSaveProductAccordingToCondition($ksOwnerCondition);
            }
        }
        return $this;
    }

    /**
     * To get the price comparison product id of the price collection
     * @param ksCollection
     * @return array
     */
    private function getKsPriceComparisonProductId($ksCollection)
    {
        $ksPriceComparisonProductId = [];
        // Iterate over the product collection
        foreach ($ksCollection as $ksRecord) {
            // Get the Product Id
            $ksPriceComparisonProductId[] = $ksRecord->getKsProductId();
        }
        return $ksPriceComparisonProductId;
    }
}
