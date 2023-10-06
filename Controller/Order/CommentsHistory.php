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

/**
 * CommentsHistory Controller
 */
class CommentsHistory extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var SellerHelper 
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var OrderRepository
     */
    protected $ksOrderRepository;

    /**
     * @var OrderManagementInterface
     */
    protected $ksOrderManagement;

    /**
     * @var SessionManagerInterface
     */
    protected $ksSession;

    /**
     * Initialize dependencies
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     * @param \Magento\Framework\Registry $ksRegistry
     * @param ksSellerHelper $ksSellerHelper
     * @param OrderManagementInterface $ksOrderManagement
     * @param SessionManagerInterface $ksSession
     */
    public function __construct( 
        \Magento\Framework\App\Action\Context $context,      
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository,
        \Magento\Framework\Registry $ksRegistry,
        KsSellerHelper $ksSellerHelper,
        OrderManagementInterface $ksOrderManagement,
        \Magento\Framework\Session\SessionManagerInterface $ksSession
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksRegistry = $ksRegistry;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksOrderManagement = $ksOrderManagement;
        $this->ksSession = $ksSession;
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
            $order = $this->ksOrderRepository->get($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->ksSession->start();
        $this->ksSession->unsOrderId(); 
        $this->ksSession->setOrderId($id);

        $this->ksRegistry->register('sales_order', $order);
        $this->ksRegistry->register('current_order', $order);
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
                    $resultPage = $this->ksResultPageFactory->create();
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