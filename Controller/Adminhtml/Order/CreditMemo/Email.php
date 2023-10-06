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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\Creditmemo;

/**
 * Class Email
 */
class Email extends \Magento\Backend\App\Action
{
    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $ks_creditmemoId = $this->getRequest()->getParam('ks_creditmemo_id');
        if (!$creditmemoId) {
            return;
        }
        $this->_objectManager->create(\Magento\Sales\Api\CreditmemoManagementInterface::class)
            ->notify($creditmemoId);

        $this->messageManager->addSuccessMessage(__('You sent the message.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('multivendor/order_creditmemo/view', ['creditmemo_id' => $ks_creditmemoId]);
        return $resultRedirect;
    }
}
