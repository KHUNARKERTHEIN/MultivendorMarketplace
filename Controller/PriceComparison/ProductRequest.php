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

namespace Ksolves\MultivendorMarketplace\Controller\PriceComparison;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * Class ProductRequest
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
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $ksSession;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * ProductRequest constructor.
     * @param Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Magento\Customer\Model\SessionFactory $ksSession
     * @param KsSellerFactory $ksSellerFactory
     * @param KsDataHelper $ksDataHelper
     */
    public function __construct(
        Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Magento\Customer\Model\SessionFactory $ksSession,
        KsSellerFactory $ksSellerFactory,
        KsDataHelper $ksDataHelper
    ) {
        $this->ksProductFactory = $ksProductFactory;
        $this->ksDate = $ksDate;
        $this->ksSession = $ksSession;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksProductId = $this->getRequest()->getParam('entity_id');

        $ksProductStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING;

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
                    if ($ksProductAutoApproval) {
                        $ksModel->setKsProductApprovalStatus(\Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED);
                    } else {
                        if ($ksModel->getKsEditMode() == 1) {
                            if (!$this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_product_edit_approval_required')) {
                                $ksModel->setKsProductApprovalStatus(\Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED);
                            } else {
                                $ksModel->setKsProductApprovalStatus(\Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING_UPDATE);
                            }
                        } else {
                            if (!$this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_product_approval_required')) {
                                $ksModel->setKsProductApprovalStatus(\Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED);
                            } else {
                                $ksModel->setKsProductApprovalStatus($ksProductStatus);
                            }
                        }
                    }
                }
                $ksModel->setKsRejectionReason("");
                $ksModel->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksModel->save();
                $this->messageManager->addSuccessMessage(__("Product request has been send successfully"));
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
