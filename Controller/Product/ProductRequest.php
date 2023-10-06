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

namespace Ksolves\MultivendorMarketplace\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Controller\Product\Save as ProductSaveController;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * Class ProductRequest
 * @package Ksolves\MultivendorMarketplace\Controller\Product
 */
class ProductRequest extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $ksSession;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksCoreProductFactory;

    /**
     * @var ProductSaveController
     */
    protected $ksProductSaveController;

    /**
     * ProductRequest constructor.
     * @param Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Magento\Customer\Model\SessionFactory $ksSession
     * @param KsSellerFactory $ksSellerFactory
     * @param KsDataHelper $ksDataHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Magento\Catalog\Model\ProductFactory $ksCoreProductFactory
     * @param ProductSaveController $ksProductSaveController
     */
    public function __construct(
        Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Magento\Customer\Model\SessionFactory $ksSession,
        KsSellerFactory $ksSellerFactory,
        KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Magento\Catalog\Model\ProductFactory $ksCoreProductFactory,
        ProductSaveController $ksProductSaveController
    ) {
        $this->ksProductFactory = $ksProductFactory;
        $this->ksDate = $ksDate;
        $this->ksSession = $ksSession;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksCoreProductFactory = $ksCoreProductFactory;
        $this->ksProductSaveController = $ksProductSaveController;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksProductId = $this->getRequest()->getParam('entity_id');
        $ksSellerId = $this->ksSession->create()->getId();

        $ksProductAutoApproval = $this->ksSellerFactory->create()
            ->load($ksSellerId, 'ks_seller_id')
            ->getKsProductAutoApproval();

        try {
            //check product Id
            if ($ksProductId) {
                // load model
                $ksModel = $this->ksProductFactory->create()->load($ksProductId, 'ks_product_id');
                //check model
                if ($ksModel) {
                    $ksEditModeStatus = $ksModel->getKsEditMode();
                    if ($ksProductAutoApproval) {
                        $ksModel->setKsProductApprovalStatus(\Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED);
                        $ksModel->setKsEditMode(1);
                        $ksModel->setKsRejectionReason("");
                    } else {
                        if ($ksModel->getKsEditMode() == 1) {
                            if (!$this->ksDataHelper->getKsConfigProductSetting('ks_update_approval') && $this->ksDataHelper->getKsConfigProductSetting('ks_admin_approval')) {
                                $ksModel->setKsProductApprovalStatus(KsProduct::KS_STATUS_APPROVED);
                                $ksModel->setKsEditMode(1);
                                $ksModel->setKsRejectionReason("");
                            } else {
                                $ksModel->setKsProductApprovalStatus(KsProduct::KS_STATUS_PENDING_UPDATE);
                            }
                        } else {
                            $ksModel->setKsProductApprovalStatus(KsProduct::KS_STATUS_PENDING);
                        }
                    }
                }
                $ksModel->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksModel->save();

                $ksProduct = $this->ksCoreProductFactory->create()->load($ksProductId);
                //send admin email to product request

                if ($ksModel->getKsProductApprovalStatus()==KsProduct::KS_STATUS_PENDING_UPDATE || $ksModel->getKsProductApprovalStatus() == KsProduct::KS_STATUS_PENDING) {
                    $this->ksProductSaveController->ksSendEmailProductRequest($ksProduct);
                }

                if ($ksModel->getKsProductApprovalStatus() == \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED) {
                    $this->ksEmailHelper->ksSendEmailProductApprove($ksSellerId, array($ksProductId));
                    $this->messageManager->addSuccessMessage(__("A product has been approved successfully."));
                }
            } else {
                $this->messageManager->addErrorMessage(__("Something went wrong while sending product request."));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
