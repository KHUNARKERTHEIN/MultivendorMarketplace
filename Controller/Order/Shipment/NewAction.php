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

namespace Ksolves\MultivendorMarketplace\Controller\Order\Shipment;

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
 * Create new shipment action.
 */
class NewAction extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @var InvoiceService
     */
    protected $ksInvoiceService;

    /**
     * @var OrderRepositoryInterface
     */
    protected $ksOrderRepository;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $ksShipmentLoader;

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
     * @param PageFactory $ksResultPageFactory
     * @param Registry $ksRegistry
     * @param InvoiceService $ksInvoiceService
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $ksShipmentLoader
     * @param KsProductHelper $ksProductHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsDataHelper $ksDataHelper
     * @param KsSalesOrderItem $ksOrderItemModel
     * @param OrderRepositoryInterface|null $ksOrderRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        PageFactory $ksResultPageFactory,
        Registry $ksRegistry,
        InvoiceService $ksInvoiceService,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $ksShipmentLoader,
        KsProductHelper $ksProductHelper,
        KsOrderHelper $ksOrderHelper,
        KsDataHelper $ksDataHelper,
        KsSalesOrderItem $ksOrderItemModel,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksRegistry = $ksRegistry;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksInvoiceService = $ksInvoiceService;
        $this->ksShipmentLoader = $ksShipmentLoader;
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
     * @return \Magento\Frontend\Model\View\Result\Redirect
     */
    protected function _redirectToOrder($orderId)
    {
        /** @var \Magento\Frontend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $orderId]);
        return $resultRedirect;
    }

    
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $ksOrder = $this->ksOrderRepository->get($orderId);
        $ksIsSeller = $this->ksOrderHelper->ksIsSellerOrder($orderId);
        $ksAllowSeller = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_shipment_settings/ks_create_shipment', $this->ksDataHelper->getKsCurrentStoreView());
        if ($ksIsSeller && $ksAllowSeller) {
            $ksShipmentItems =[];
            $this->ksRegistry->register('current_order', $ksOrder);
            $this->ksShipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->ksShipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $ksTotalQty=0;
            /*prepare array for shipment*/
            foreach ($ksOrder->getAllItems() as $ksOrderItem) {
                if ($this->ksProductHelper->isKsSellerProduct($ksOrderItem->getProductId())) {
                    $ksShipmentItems['items'][$ksOrderItem->getId()] =  $this->ksOrderItemModel->loadByKsOrderItemId($ksOrderItem->getId())->getKsQtyToShip();
                    $ksTotalQty += $ksShipmentItems['items'][$ksOrderItem->getId()];
                }
            }
            if (!($ksTotalQty>0)) {
                $this->messageManager->addErrorMessage(__("The shipment can't be created without products. Add products and try again."));
                return $this->_redirectToOrder($orderId);
            }
            $this->ksShipmentLoader->setShipment($ksShipmentItems);
            $this->ksShipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->ksShipmentLoader->load();
            if (!$shipment) {
                return $this->resultRedirectFactory->create()->setPath('multivendor/order/vieworder', ['ks_order_id' => $orderId]);
            }
            if ($shipment) {
                $comment = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getCommentText(true);
                if ($comment) {
                    $shipment->setCommentText($comment);
                }

                $this->_view->loadLayout();
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipments'));
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Shipment'));
                $this->_view->renderLayout();
            } else {
                return $this->_redirectToOrder($orderId);
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('You are not authorized to create a shipment for the order')
            );
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/create',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
