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

namespace Ksolves\MultivendorMarketplace\Controller\Order\Invoice;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem;

/**
 * Create new invoice action.
 */
class NewAction extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var InvoiceService
     */
    protected $ksInvoiceService;

    /**
     * @var OrderRepositoryInterface
     */
    protected $ksOrderRepository;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsSalesOrderItem
     */
    protected $ksOrderItemModel;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param Registry $ksRegistry
     * @param PageFactory $ksResultPageFactory
     * @param InvoiceService $ksInvoiceService
     * @param KsProductHelper $ksProductHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsDataHelper $ksDataHelper
     * @param KsSalesOrderItem $ksOrderItemModel
     * @param OrderRepositoryInterface|null $ksOrderRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        Registry $ksRegistry,
        PageFactory $ksResultPageFactory,
        InvoiceService $ksInvoiceService,
        KsProductHelper $ksProductHelper,
        KsOrderHelper $ksOrderHelper,
        KsDataHelper $ksDataHelper,
        KsSalesOrderItem $ksOrderItemModel,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksInvoiceService = $ksInvoiceService;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksProductHelper = $ksProductHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksOrderItemModel = $ksOrderItemModel;
        parent::__construct($ksContext);
    }

    /**
     * Redirect to order view page
     *
     * @param int $orderId
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function ksRedirectToOrder($orderId)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $orderId]);
        return $resultRedirect;
    }

    /**
     * Invoice create page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksOrderId = $this->getRequest()->getParam('order_id');
        $ksInvoiceData = $this->getRequest()->getParam('invoice', []);
        $ksInvoiceItems = $ksInvoiceData['items'] ?? [];
        $ksIsSeller = $this->ksOrderHelper->ksIsSellerOrder($ksOrderId);
        $ksAllowSeller = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_invoice_settings/ks_create_invoice', $this->ksDataHelper->getKsCurrentStoreView());
        if ($ksAllowSeller && $ksIsSeller) {
            try {
                /** @var \Magento\Sales\Model\Order $ksOrder */
                $ksOrder = $this->ksOrderRepository->get($ksOrderId);
                /*check for seller's products*/
                foreach ($ksOrder->getAllItems() as $ksOrderItem) {
                    if ($this->ksProductHelper->isKsSellerProduct($ksOrderItem->getProductId())) {
                        $ksInvoiceItems[$ksOrderItem->getId()] = $this->ksOrderItemModel->loadByKsOrderItemId($ksOrderItem->getId())->getKsQtyToInvoice();
                    }
                }

                if (!$ksOrder->canInvoice()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The order does not allow an invoice to be created.')
                    );
                }
                $invoice = $this->ksInvoiceService->prepareInvoice($ksOrder, $ksInvoiceItems);
                if (!($invoice->getTotalQty()>0)) {
                    $this->messageManager->addErrorMessage(
                        __("The invoice can't be created for the order.")
                    );
                    return $this->resultRedirectFactory->create()->setPath('multivendor/order/vieworder', ['ks_order_id' => $ksOrderId]);
                }
                $this->ksRegistry->register('current_order', $ksOrder);
                $this->ksRegistry->register('current_invoice', $invoice);

                $comment = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getCommentText(true);
                if ($comment) {
                    $invoice->setCommentText($comment);
                }

                /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
                $resultPage = $this->ksResultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
                $resultPage->getConfig()->getTitle()->prepend(__('New Invoice'));
                return $resultPage;
            } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $this->ksRedirectToOrder($ksOrderId);
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage($exception, 'Cannot create an invoice.');
                return $this->ksRedirectToOrder($ksOrderId);
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('You are not authorized to create an invoice for the order')
            );
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/create',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
