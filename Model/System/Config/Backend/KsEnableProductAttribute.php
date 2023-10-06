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

namespace Ksolves\MultivendorMarketplace\Model\System\Config\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * Backend for serialized array data
 */
class KsEnableProductAttribute extends \Magento\Framework\App\Config\Value
{
    /**
     * XML Path
     */
    const XML_PATH_PRODUCT_ATTRIBUTE_REJECTION_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_rejection_email_template';

    /**
     * @var CollectionFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $ksAttributeFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $ksRequest;

    /**
     * @var WriterInterface
     */
    protected $ksConfigWriter;

    /**
    * @param Context $context,
    * @param Registry $registry
    * @param ScopeConfigInterface $config
    * @param TypeListInterface $cacheTypeList
    * @param ManagerInterface $ksMessageManager
    * @param CollectionFactory $ksSellerFactory
    * @param KsSellerHelper $ksSellerHelper
    * @param WriterInterface $ksConfigWriter
    * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $ksAttributeFactory
    * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
    * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
    * @param \Magento\Framework\App\RequestInterface $ksRequest
    * @param AbstractResource $resource = null
    * @param AbstractDb $resourceCollection = null
    * @param array $data = []
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CollectionFactory $ksSellerFactory,
        KsSellerHelper $ksSellerHelper,
        KsProductHelper $ksProductHelper,
        WriterInterface $ksConfigWriter,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $ksAttributeFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Magento\Framework\App\RequestInterface $ksRequest,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksConfigWriter = $ksConfigWriter;
        $this->ksAttributeFactory = $ksAttributeFactory;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksRequest = $ksRequest;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $ksAutoApproval = $this->getValue();
            if ((int) $ksAutoApproval==0) {
                $this->ksConfigWriter->save('ks_marketplace_catalog/ks_product_attribute_settings/ks_admin_approval_attribute_status', 0, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
                $this->ksConfigWriter->save('ks_marketplace_catalog/ks_product_attribute_settings/ks_admin_approval_update_attribute_status', 0, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
            }
            $this->ksSetConfigurationValueinSellerTable((int)$ksAutoApproval);
        }
        return parent::afterSave();
    }

    /**
     * Save Attribute Values in the Database
     * @param  $ksAttributeEnable
     * @return void
     */
    public function ksSetConfigurationValueinSellerTable($ksAttributeEnable)
    {
        // Get Seller List
        $ksSellerList = $this->ksSellerHelper->getKsSellerList();
        // Iterate Seller List
        foreach ($ksSellerList as $ksSellerId) {
            // Get the Model for the Save
            $ksSellerModel = $this->ksSellerFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
            $ksSellerModel->setKsProductAttributeRequestAllowedStatus($ksAttributeEnable);
            if ($ksAttributeEnable == 0) {
                $ksSellerModel->setKsProductAttributeAutoApprovalStatus($ksAttributeEnable);
            }
            $ksSellerModel->save();
            if ($ksAttributeEnable == 0) {
                $this->ksRejectSellerAttribute($ksSellerId);
            }
        }
    }

    /**
     * Reject All the Attribute Which are pending update and pending rejected
     *
     * @param $ksSellerId
     * @return void
     */
    public function ksRejectSellerAttribute($ksSellerId)
    {
        // Reject Status
        $ksRejectStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_REJECTED;
        // Collection of Attributes of Seller which are pending new and pending update
        $ksAttributeColl = $this->ksAttributeFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_attribute_approval_status', ['in' => [0, 3]]);
        // Iterate the collection
        foreach ($ksAttributeColl as $ksCollection) {
            // Get the Attribute Id
            $ksAttributeId = $ksCollection->getAttributeId();
            // Get Collection According to Attribute
            $ksAttributeModel = $this->ksAttributeFactory->create()->addFieldToFilter('attribute_id', $ksAttributeId)->getFirstItem();
            // update Status
            $ksAttributeModel->setKsAttributeRejectionReason("N/A");
            $ksAttributeModel->setKsAttributeApprovalStatus($ksRejectStatus);
            $ksAttributeModel->save();

            //Email functionality
            $ksStoreId = $this->ksRequest->getParam('store', 0);
            $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_rejection_email_template', $ksStoreId);
            if ($ksEmailDisable != 'disable' && $ksAttributeModel->getKsAttributeApprovalStatus() == $ksRejectStatus) {
                $ksSellerDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
                $ksSender = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_sender_email', $ksStoreId);
                
                $ksTemplateVariable = [];
                $ksTemplateVariable['ks-seller-name'] = $ksSellerDetails["name"];
                $ksTemplateVariable['ks_seller_email'] = $ksSellerDetails["email"];
                $ksTemplateVariable['ks-product-attribute-name'] = $ksAttributeModel->getFrontendLabel();
                $ksTemplateVariable['ks-attribute-code'] = $ksAttributeModel->getAttributeCode();
                $ksTemplateVariable['ks-required'] = $this->ksSellerHelper->getKsYesNoValue($ksAttributeModel->getIsRequired());
                $ksTemplateVariable['ks-system'] = $this->ksSellerHelper->getKsYesNoValue($ksAttributeModel->getIsUserDefined());
                $ksTemplateVariable['ks-visible'] = $this->ksSellerHelper->getKsYesNoValue($ksAttributeModel->getIsVisible());
                $ksTemplateVariable['ks-scope'] = $this->ksSellerHelper->getKsStoreValues($ksAttributeModel->getIsGlobal());
                $ksTemplateVariable['ks-searchable'] = $this->ksSellerHelper->getKsYesNoValue($ksAttributeModel->getIsSearchable());
                $ksTemplateVariable['ks-use-layer-navigation'] = $this->ksSellerHelper->getKsFilterableValues($ksAttributeModel->getIsFilterable());
                $ksTemplateVariable['ks-comparable'] = $this->ksSellerHelper->getKsYesNoValue($ksAttributeModel->getIsComparable());
                $ksTemplateVariable['ks-rejection-reason'] = "N/A";
                $this->ksEmailHelper->ksSendProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_REJECTION_MAIL, $ksTemplateVariable, $ksSender, $ksSellerDetails, 1);
            }
        }
    }
}
