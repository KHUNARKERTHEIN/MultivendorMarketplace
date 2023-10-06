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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductType\CollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

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
     * @var \Magento\Framework\App\RequestInterface,
     */
    private $ksRequest;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param RequestInterface $ksRequest
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        KsProductTypeFactory $ksProductTypeFactory,
        RequestInterface $ksRequest,
        DataPersistorInterface $ksDataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksRequest = $ksRequest;
        $this->ksProductTypeFactory = $ksProductTypeFactory;
        $this->ksDataPersistor = $ksDataPersistor;
        // Get Seller Id
        $ksSellerId = $this->ksRequest->getParam('ks_seller_id');
        // Get Seller Id from DataPersistor
        $ksDataSellerId = $this->ksDataPersistor->get('ks_current_seller_id');
        // Terinary Operator to find seller id
        $ksCurrentSellerId = $ksSellerId ? $ksSellerId : $ksDataSellerId;
        // Get the value of Global Product Type
        $ksGlobalProductType = $this->getKsProductTypeFromTable(0, $ksCollectionFactory);
        // Assigned Value
        $ksAssignStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_ASSIGNED;
        // Unassigned Value
        $ksUnassignStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_UNASSIGNED;
        // Enable Value
        $ksEnableProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;
        // Disable Value
        $ksDisableProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::DISABLE_VALUE;

        // Check the Global Product Type
        if (!empty($ksGlobalProductType)) {
            // Get the seller product type
            $ksSellerProductType = $this->getKsProductTypeFromTable($ksCurrentSellerId, $ksCollectionFactory);
            // Check seller id is empty or not
            if (!empty($ksSellerProductType)) {
                $ksDifferent = array_diff($ksGlobalProductType, $ksSellerProductType);
                // Store the different datatype in the grid
                foreach ($ksDifferent as $kskey => $ksProduct) {
                    $this->KsSaveProductTypeToDataBase($ksCurrentSellerId, $ksProduct, $ksDisableProductTypeStatus, $ksUnassignStatus);
                }
            } else {
                foreach ($ksGlobalProductType as $kskey => $ksProduct) {
                    $this->KsSaveProductTypeToDataBase($ksCurrentSellerId, $ksProduct, $ksEnableProductTypeStatus, $ksAssignStatus);
                }
            }
        }
        $ksFinalSellerProductTypeCollection = $ksCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksCurrentSellerId);
        $this->collection = $ksFinalSellerProductTypeCollection;
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
     * @param int
     * @return array
     */
    public function getKsProductTypeFromTable($ksSellerId, $ksCollectionFactory)
    {
        //Getting Collection of Seller Product Type
        $ksProductTypeCollection = $ksCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
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
    public function KsSaveProductTypeToDataBase($ksSellerId, $ksProductType, $ksProductStatus, $ksRequestStatus)
    {
        $ksSetSellerProduct = $this->ksProductTypeFactory->create();
        $ksSetSellerProduct->setKsSellerId($ksSellerId)
                            ->setKsProductType($ksProductType)
                            ->setKsProductTypeStatus($ksProductStatus)
                            ->setKsRequestStatus($ksRequestStatus)
                            ->save();
    }
}
