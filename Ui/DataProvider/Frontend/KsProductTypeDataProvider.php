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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Frontend;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductType\CollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsProductTypeDataProvider Ui Class
 *
 */
class KsProductTypeDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * ProductType collection
     *
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductType\Collection
     */
    protected $collection;

    /**
     * @var KsProductTypeFactory
     */
    protected $ksProductTypeFactory;
    
    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksProductCollectionFactory
     * @param KsProductTypeFactory $ksProductTypeFactory
     * @param array $meta
     * @param array $data
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksProductCollectionFactory,
        KsProductTypeFactory $ksProductTypeFactory,
        KsSellerHelper $ksSellerHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksProductTypeFactory = $ksProductTypeFactory;
        //Getting Seller Id
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        $ksGlobalProductType = $this->getKsProductTypeFromDatabase(0, $ksProductCollectionFactory);
        // Assigned Value
        $ksAssignStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_ASSIGNED;
        // Unassigned Value
        $ksUnassignStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_UNASSIGNED;
        // Enable Value
        $ksEnableProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;
        // Disable Value
        $ksDisableProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::DISABLE_VALUE;
        if (!empty($ksGlobalProductType)) {
            // Get Seller Product Types
            $ksSellerProductType = $this->getKsProductTypeFromDatabase($ksSellerId, $ksProductCollectionFactory);
            // Check Seller Product Type
            if (!empty($ksSellerProductType)) {
                $ksDifferentProduct = array_diff($ksGlobalProductType, $ksSellerProductType);

                foreach ($ksDifferentProduct as $kskey => $ksProduct) {
                    $this->KsSaveProductTypeDataBase($ksSellerId, $ksProduct, $ksDisableProductTypeStatus, $ksUnassignStatus);
                }
            } else {
                foreach ($ksGlobalProductType as $kskey => $ksProduct) {
                    $this->KsSaveProductTypeDataBase($ksSellerId, $ksProduct, $ksEnableProductTypeStatus, $ksAssignStatus);
                }
            }
        }
        $ksSellerProductTypeCollection = $ksProductCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        $this->collection = $ksSellerProductTypeCollection;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        
        $ksItems = $this->getCollection()->toArray();
        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' =>  $ksItems['items'],
        ];
    }

    /**
     * Get Product Type
     * @param int $ksSellerId
     * @param object $ksProductCollectionFactory
     * @return array
     */
    public function getKsProductTypeFromDatabase($ksSellerId, $ksProductCollectionFactory)
    {
        //Getting Collection of Seller Product Type
        $ksProductTypeCollection = $ksProductCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        // Intialize array
        $ksProductType = [];
        // Check Collection has size
        if ($ksProductTypeCollection) {
            foreach ($ksProductTypeCollection as $kskey => $ksProduct) {
                $ksProductType[] = $ksProduct->getKsProductType();
            }
        }
        return $ksProductType;
    }

    /**
     * Save Product Type in DataBase
     * @param int $ksSellerId
     * @param string $ksProductType
     * @param int $ksStatus
     * @param int $ksRequestStatus
     */
    public function KsSaveProductTypeDataBase($ksSellerId, $ksProductType, $ksProductStatus, $ksRequestStatus)
    {
        $ksSetSellerProduct = $this->ksProductTypeFactory->create();
        $ksSetSellerProduct->setKsSellerId($ksSellerId)
                            ->setKsProductType($ksProductType)
                            ->setKsProductTypeStatus($ksProductStatus)
                            ->setKsRequestStatus($ksRequestStatus)
                            ->save();
    }
}
