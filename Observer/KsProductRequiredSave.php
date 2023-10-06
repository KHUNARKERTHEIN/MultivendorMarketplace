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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * KsProductRequiredSave Observer Class
 */
class KsProductRequiredSave implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $ksRequest;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var CollectionFactory
     */
    protected $ksSellerFactory;

    /**
     * @var WriterInterface
     */
    protected $ksConfigWriter;

    /**
     * @param RequestInterface $ksRequest
     * @param ManagerInterface $ksMessageManager
     * @param KsSellerFactory $ksSellerFactory
     * @param WriterInterface $ksConfigWriter
     */
    public function __construct(
        RequestInterface $ksRequest,
        ManagerInterface $ksMessageManager,
        CollectionFactory $ksSellerFactory,
        WriterInterface $ksConfigWriter
    ) {
        $this->ksRequest = $ksRequest;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksConfigWriter = $ksConfigWriter;
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


            // Get Product required approval config data
            $ksAutoApproval = $ksFieldData['ks_product_settings']['fields']['ks_admin_approval']['value'];

            if ((int) $ksAutoApproval==0) {
                $this->ksConfigWriter->save('ks_marketplace_catalog/ks_product_settings/ks_update_approval', 0, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
            }

            $this->ksProductAutoApprovalSeller($ksAutoApproval);
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
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
