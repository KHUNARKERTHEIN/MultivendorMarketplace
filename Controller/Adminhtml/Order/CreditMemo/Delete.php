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
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\CreditMemo;

use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemoFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Delete Credit Memo Controller
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var KsSalesCreditMemoFactory
     */
    protected $ksSalesCreditMemoFactory;

    /**
     * @param Context $ksContext
     * @param KsSalesCreditMemoFactory $ksSalesCreditMemoFactory
     */
    public function __construct(
        Context $ksContext,
        KsSalesCreditMemoFactory $ksSalesCreditMemoFactory
    ) {
        $this->ksSalesCreditMemoFactory = $ksSalesCreditMemoFactory;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksCreditMemoId = $this->getRequest()->getParam('creditmemo_id');

        try {
            $this->ksSalesCreditMemoFactory->create()->load($ksCreditMemoId)->delete();
            $this->messageManager->addSuccessMessage(__('Credit memo request has been deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        //for redirecting url
        return $ksResultRedirect;
    }
}
