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
use Magento\Framework\App\RequestInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Backend for serialized array data
 */
class KsProductRequiredSave extends \Magento\Framework\App\Config\Value
{
    /**
     * @var RequestInterface
     */
    private $ksRequest;

    /**
     * @var CollectionFactory
     */
    protected $ksSellerFactory;

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
        RequestInterface $ksRequest,
        CollectionFactory $ksSellerFactory,
        WriterInterface $ksConfigWriter,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ksRequest = $ksRequest;
        $this->ksSellerFactory = $ksSellerFactory;
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

            if ((int) $ksAutoApproval==0) {
                $this->ksConfigWriter->save('ks_marketplace_catalog/ks_product_settings/ks_update_approval', 0, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
            }
            $this->ksProductAutoApprovalSeller($ksAutoApproval);
        }
        return parent::afterSave();
    }

    /**
    * Save seller Product Auto Approval
     * @param  $ksAutoApproval
     * @return void
     */
    public function ksProductAutoApprovalSeller($ksAutoApproval)
    {
        if ($ksAutoApproval != '') {
            $ksSellerAutoApproval = ((int) $ksAutoApproval==1) ? 0 : 1;
        }

        // Get Seller List
        $ksSellerList = $this->ksSellerFactory->create();
        // Iterate Seller List
        foreach ($ksSellerList as $ksSeller) {
            // Get the Model for the Save
            $ksSellerId = $ksSeller->getKsSellerId();
            $ksSellerModel = $this->ksSellerFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
            $ksSellerModel->setKsProductAutoApproval($ksSellerAutoApproval);
            $ksSellerModel->save();
        }
    }
}
