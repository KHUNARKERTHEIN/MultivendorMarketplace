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
 * EditReason Controller class
 *
 * Class EditReason
 */
class EditReason extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksSession;

    /**
     * EditReason constructor.
     *
     * @param Action\Context $ksContext
     * @param \Magento\Customer\Model\Session $ksSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Customer\Model\Session $ksSession
    ) {
        $this->ksSession = $ksSession;
        parent::__construct($ksContext);
    }
    
    /**
     * Edit Reason
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $reasonId = $this->getRequest()->getParam('id');
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($reasonId) {
            // set reason id in session
            $this->ksSession->setKsCurrentReasonId($reasonId);
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Reason'));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Add Reason'));
        }
       
        return $resultPage;
    }
}
