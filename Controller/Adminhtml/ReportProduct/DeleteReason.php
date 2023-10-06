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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ReportProduct;

use Magento\Framework\Controller\ResultFactory;

/**
 * DeleteReason Controller class
 *
 * Class DeleteReason
 */
class DeleteReason extends \Magento\Backend\App\Action
{
    /**
     * KsReportProductReasonsFactory
     *
     * @var \Ksolves\MultivendorMarketplace\Model\KsReportProductReasonsFactory
     */
    protected $ksReasonFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksSession;
    
    /**
     * Delete constructor.
     *
     * @param Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsReportProductReasonsFactory $ksReasonFactory
     * @param \Magento\Customer\Model\Session $ksSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsReportProductReasonsFactory $ksReasonFactory,
        \Magento\Customer\Model\Session $ksSession
    ) {
        $this->ksReasonFactory = $ksReasonFactory;
        $this->ksSession = $ksSession;
        parent::__construct($ksContext);
    }

    /**
     * DeleteReason action
     */
    public function execute()
    {
        try {
            /*get current reason id*/
            $ksId = $this->ksSession->getKsCurrentReasonId();
            $this->ksSession->unsKsCurrentReasonId();
            $ksCurrentReason = $this->ksReasonFactory->create()->getCollection()->addFieldToFilter('id', $ksId)->getFirstItem();
            /*Fetch all reason entries and delete*/
            $ksCollection = $this->ksReasonFactory->create()->getCollection()->addFieldToFilter('ks_reason', $ksCurrentReason->getKsReason());
            foreach ($ksCollection as $ksRecord) {
                $ksRecord->delete();
            }
            $this->messageManager->addSuccess(
                __('The Reason was deleted successfully.')
            );
            $this->_redirect('multivendor/reportproduct/index');
            return;
        } catch (Exception $e) {
            $this->messageManager->addError(
                __('Something went wrong while saving the item data. Please review the error log.')
            );
            $this->_redirect('multivendor/reportproduct/index');
            return;
        }
    }
}
