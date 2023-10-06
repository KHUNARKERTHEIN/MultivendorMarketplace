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

namespace Ksolves\MultivendorMarketplace\Controller\Order;

use Magento\Framework\App\Action\Action;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper as KsSellerHelper;
use Magento\Sales\Api\OrderManagementInterface;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrder;

/**
 * ViewOrder Controller
 */
class ViewOrder extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var SellerHelper 
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var orderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var SessionManagerInterface 
     */
    protected $session;

    /**
     * @var KsSalesOrder
     */
    protected $ksSalesOrder;

    /**
     * Initialize dependencies
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Registry $registry
     * @param VendorHelper $ksSellerHelper
     * @param OrderManagementInterface $orderManagement
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param KsSalesOrder $ksSalesOrder
     */
    public function __construct( 
        \Magento\Framework\App\Action\Context $context,      
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Registry $registry,
        KsSellerHelper $ksSellerHelper,
        OrderManagementInterface $orderManagement,
        \Magento\Framework\Session\SessionManagerInterface $session,
        KsSalesOrder $ksSalesOrder
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->orderManagement = $orderManagement;
        $this->session = $session;
        $this->ksSalesOrder = $ksSalesOrder;
        parent::__construct($context);
    }

     /**
     * Initialize order model instance
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('ks_order_id');
        $ksIsSellerOrder = $this->ksSellerHelper->KsIsSellerOrder($id);
        if(!$ksIsSellerOrder)
                return false;  
        try {
            $order = $this->orderRepository->get($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->session->start();
        $this->session->unsOrderId(); 
        $this->session->setOrderId($id);

        $this->registry->register('sales_order', $order);
        $this->registry->register('current_order', $order);
        return $order;
    } 
     
     
     /**
     * Seller Order View Action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
     public function execute()
     { 
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        /* condition to check seller */
        if ($ksIsSeller == 1) {
            $order = $this->_initOrder();
            if ($order) {
                try { 
                    $resultPage = $this->_resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->prepend(sprintf("Manage Order %s", $order->getIncrementId()));
                    return $resultPage;
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Exception occurred during order load'));
                    
                    return $this->resultRedirectFactory->create()->setPath(
                                '*/*/listing',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
                }  
            }else{
                return $this->resultRedirectFactory->create()->setPath(
                                '*/*/listing',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/create',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}