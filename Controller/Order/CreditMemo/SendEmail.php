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

namespace Ksolves\MultivendorMarketplace\Controller\Order\CreditMemo;

use Magento\Framework\App\Action\Action;

/**
 * SendEmail Controller
 */
class SendEmail extends Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $ksResultForwardFactory;
    
    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface
     */
    protected $ksMemoManagement;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $ksMemoManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Sales\Api\CreditmemoManagementInterface $ksMemoManagement
    ) {
        $this->ksMemoManagement = $ksMemoManagement;
        parent::__construct($ksContext);
    }

    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $ksMemoId = $this->getRequest()->getParam('creditmemo_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$ksMemoId) {
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        $this->ksMemoManagement->notify($ksMemoId);

        $this->messageManager->addSuccessMessage(__('You sent the message.'));
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
