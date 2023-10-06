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
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * Backend for serialized array data
 */
class KsAutoApprovalAttribute extends \Magento\Framework\App\Config\Value
{

    /**
     * @var CollectionFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var WriterInterface
     */
    protected $ksConfigWriter;

    /**
    * @param Context $context,
    * @param Registry $registry
    * @param ScopeConfigInterface $config
    * @param TypeListInterface $cacheTypeList
    * @param RequestInterface $ksRequest
    * @param ManagerInterface $ksMessageManager
    * @param CollectionFactory $ksSellerFactory
    * @param KsSellerHelper $ksSellerHelper,
    * @param WriterInterface $ksConfigWriter
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
        KsDataHelper $ksDataHelper,
        WriterInterface $ksConfigWriter,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksConfigWriter = $ksConfigWriter;
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
            $ksEnableAttribute = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_seller_enable_custom_attribute_status');
            if ($ksEnableAttribute) {
                $this->ksSetConfigurationValueinSellerTable($ksAutoApproval);
            } else {
                $this->ksSetConfigurationValueinSellerTable(1);
            }
        }
        return parent::afterSave();
    }

    /**
     * Save Attribute Values in the Database
     * @param  $ksAttributeAutoApproval
     * @param  $ksEnableCustomAttribute
     * @return void
     */
    public function ksSetConfigurationValueinSellerTable($ksAttributeAutoApproval)
    {
        if ($ksAttributeAutoApproval != '') {
            $ksSellerAutoApproval = ((int) $ksAttributeAutoApproval==1) ? 0 : 1;
        }
        // Get Seller List
        $ksSellerList = $this->ksSellerHelper->getKsSellerList();
        // Iterate Seller List
        foreach ($ksSellerList as $ksSellerId) {
            // Get the Model for the Save
            $ksSellerModel = $this->ksSellerFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
            $ksSellerModel->setKsProductAttributeAutoApprovalStatus($ksSellerAutoApproval);
            $ksSellerModel->save();
        }
    }
}
