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
use \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper as KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem;

/**
 * NewAction Controller
 */
class NewAction extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $ksOrderRepository;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $ksCreditmemoLoader;

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
     * Initialize dependencies
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     * @param \Magento\Framework\Registry $ksRegistry
     * @param KsSellerHelper $ksSellerHelper
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $ksCreditmemoLoader
     * @param KsProductHelper $ksProductHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsDataHelper $ksDataHelper
     * @param KsSalesOrderItem $ksOrderItemModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository,
        \Magento\Framework\Registry $ksRegistry,
        KsSellerHelper $ksSellerHelper,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $ksCreditmemoLoader,
        KsProductHelper $ksProductHelper,
        KsOrderHelper $ksOrderHelper,
        KsDataHelper $ksDataHelper,
        KsSalesOrderItem $ksOrderItemModel
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksRegistry = $ksRegistry;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCreditmemoLoader = $ksCreditmemoLoader;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksOrderItemModel = $ksOrderItemModel;
        parent::__construct($context);
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
     * Seller Create CreditMemo.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $ksIsSeller = $this->ksOrderHelper->ksIsSellerOrder($orderId);
        $ksAllowSeller = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_creditmemo_settings/ks_create_creditmemo', $this->ksDataHelper->getKsCurrentStoreView());
        /* condition to check seller */
        if ($ksIsSeller && $ksAllowSeller) {
            $ksOrder = $this->ksOrderRepository->get($orderId);
            $ksCreditMemoItems =[];
            $this->ksRegistry->register('current_order', $ksOrder);
            $this->ksCreditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->ksCreditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $ksTotalQty=0;
            /*prepare array for creditmemo*/
            foreach ($ksOrder->getAllItems() as $ksOrderItem) {
                if ($this->ksProductHelper->isKsSellerProduct($ksOrderItem->getProductId())) {
                    $ksCreditMemoItems['items'][$ksOrderItem->getId()]['qty'] =  $this->ksOrderItemModel->loadByKsOrderItemId($ksOrderItem->getId())->getKsQtyToRefund();
                    $ksTotalQty += $ksCreditMemoItems['items'][$ksOrderItem->getId()]['qty'];
                }
            }
            if (!($ksTotalQty>0)) {
                $this->messageManager->addErrorMessage(__("The creditmemo can't be created without products. Add products and try again."));
                return $this->ksRedirectToOrder($orderId);
            }
            if (!$ksOrder->canCreditmemo()) {
                $this->messageManager->addErrorMessage(__("The order does not allow a memo to be created."));
                return $this->ksRedirectToOrder($orderId);
            }
            $ksCreditMemoItems['shipping_amount'] = 0;
            $this->ksCreditmemoLoader->setCreditmemo($ksCreditMemoItems);
            $creditmemo = $this->ksCreditmemoLoader->load();
            if ($creditmemo) {
                $this->_view->loadLayout();
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('CreditMemo'));
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New CreditMemo'));
                $this->_view->renderLayout();
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('You are not authorized to create a credit memo for the order')
            );
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/create',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
