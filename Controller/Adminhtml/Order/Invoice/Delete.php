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
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\Invoice;

use Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Delete Invoice Controller
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var KsSalesInvoiceFactory
     */
    protected $ksSalesInvoiceFactory;

    /**
     * @param Context $ksContext
     * @param KsSalesInvoiceFactory $ksSalesInvoiceFactory
     */
    public function __construct(
        Context $ksContext,
        KsSalesInvoiceFactory $ksSalesInvoiceFactory
    ) {
        $this->ksSalesInvoiceFactory = $ksSalesInvoiceFactory;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksInvoiceId = $this->getRequest()->getParam('invoice_id');

        try {
            $this->ksSalesInvoiceFactory->create()->load($ksInvoiceId)->delete();
            $this->messageManager->addSuccessMessage(__('Invoice request has been deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        //for redirecting url
        return $ksResultRedirect;
    }
}
