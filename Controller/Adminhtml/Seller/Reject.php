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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller;

use Magento\Framework\Controller\ResultFactory;

/**
 * Reject Controller Class
 */
class Reject extends \Magento\Backend\App\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * Delete constructor.
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksDate = $ksDate;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action
     */
    public function execute()
    {
        $ksSellerStatus = \Ksolves\MultivendorMarketplace\Model\KsSeller::KS_STATUS_REJECTED;
        $ksSellerStoreStatus = \Ksolves\MultivendorMarketplace\Model\KsSellerStore::KS_STATUS_DISABLED;
        $ksId = $this->getRequest()->getParam('id');

        //check data
        if ($ksId) {
            //get model data
            $ksModel = $this->ksSellerFactory->create()->load($ksId);
            //check model data
            if ($ksModel) {
                $ksModel->setKsRejectionReason($this->getRequest()->getParam('ks_reject_message'));
                $ksModel->setKsSellerStatus($ksSellerStatus);
                $ksModel->setKsStoreStatus($ksSellerStoreStatus);
                $ksModel->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksModel->save();
                
                $this->messageManager->addSuccessMessage(
                    __('A seller has been rejected successfully.')
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while rejecting seller.')
                );
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while rejecting seller.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
