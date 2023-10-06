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

use Magento\Framework\Event\ObserverInterface;
use Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;

/**
 * KsProductTypeDataSave Observer Class
 */
class KsProductTypeDataSave implements ObserverInterface
{
    /**
     * @var KsProductTypeFactory
     */
    protected $ksProductTypeFactory;

    /**
     * @var RequestInterface
     */
    private $ksRequest;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var CollectionFactory
     */
    protected $ksSellerFactory;

    /**
     * @param RequestInterface $ksRequest,
     * @param KsProductTypeFactory $ksProductTypeFactory,
     * @param KsDataHelper $ksDataHelper,
     * @param ManagerInterface $ksMessageManager
     * @param KsProductTypeHelper $ksProductTypeHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param KsSellerFactory $ksSellerFactory
     */
    public function __construct(
        RequestInterface $ksRequest,
        KsProductTypeFactory $ksProductTypeFactory,
        KsDataHelper $ksDataHelper,
        ManagerInterface $ksMessageManager,
        KsProductTypeHelper $ksProductTypeHelper,
        KsSellerHelper $ksSellerHelper,
        CollectionFactory $ksSellerFactory
    ) {
        $this->ksProductTypeFactory    = $ksProductTypeFactory;
        $this->ksRequest = $ksRequest;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSellerFactory = $ksSellerFactory;
    }

    /**
     * Getting Product Type When Configuration Page Save
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            // Get the Field Of the System.xml File
            $ksFieldData = $this->ksRequest->getParam('groups');
            try {
                // Get The details of Product Type Selected by the admin
                $ksProductTypes = $ksFieldData['ks_product_type_settings']['fields']['ks_product_type']['value'];
            } catch (\Exception $e) {
                $ksProductTypes = [];
            }

            // Assigned Value
            $ksAssignStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_ASSIGNED;
            // Enable Value
            $ksEnableStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;
            // Check Types are empty or not
            if (!empty($ksProductTypes)) {
                $this->ksLogicForSavingData($ksEnableStatus, $ksAssignStatus, $ksProductTypes);
            }
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
    }

    /**
     * Save Product Type into Database
     * @param string $ksProductType
     * @param int $ksProductStatus
     * @param int $ksRequestStatus
     */
    public function ksSaveProductType($ksProductType, $ksProductStatus, $ksRequestStatus)
    {
        $ksGlobalType = $this->ksProductTypeFactory->create();
        $ksGlobalType->setKsSellerId(0)
                  ->setKsProductType($ksProductType)
                  ->setKsProductTypeStatus($ksProductStatus)
                  ->setKsRequestStatus($ksRequestStatus)
                  ->save();
    }

    /**
     * Logic for Save Data
     * @param int $ksEnableProductTypeStatus
     * @param int $ksAssignStatus
     * @param array $ksProductTypes
     */
    public function ksLogicForSavingData($ksEnableStatus, $ksAssignStatus, $ksProductTypes)
    {
        $ksTypeCollection = $this->ksProductTypeFactory->create()->getCollection();
        // Save Product Type in the Database for the first time
        if ($ksTypeCollection->getSize() == 0) {
            foreach ($ksProductTypes as $kskey => $ksProducttypename) {
                $this->ksSaveProductType($ksProducttypename, $ksEnableStatus, $ksAssignStatus);
            }
        } else {
            // Get Collection
            $ksGlobalCollection = $this->ksProductTypeFactory->create()->getCollection();
            $ksProduct = [];
            // Store all 0 product type in array
            foreach ($ksGlobalCollection as $ksRecord) {
                if ($ksRecord->getKsSellerId() == 0) {
                    $ksProduct[] = $ksRecord->getKsProductType();
                }
            }
            // Check which value is not present in database
            $ksDifferentProduct = array_diff($ksProduct, $ksProductTypes);
            // If present delete that value
            if (!empty($ksDifferentProduct)) {
                foreach ($ksGlobalCollection as $ksRecord) {
                    $ksProducttypename = $ksRecord->getKsProductType();
                    if (in_array($ksProducttypename, $ksDifferentProduct)) {
                        $ksRecord->delete();
                    }
                }
                //disabled product with unassign product types
                $this->ksProductTypeHelper->disableKsUnassignTypeProductIds(null, $ksDifferentProduct);
            }
            // Save the value of again save config product
            foreach ($ksProductTypes as $kskey => $ksRecord) {
                if (!in_array($ksRecord, $ksProduct)) {
                    $this->ksSaveProductType($ksRecord, $ksEnableStatus, $ksAssignStatus);
                }
            }
        }
    }
}
