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

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * AddComment Controller
 */
class AddComment extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $ksResultJsonFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $ksOrderRepository;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Initialize dependencies
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct( 
        \Magento\Framework\App\Action\Context $ksContext,      
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        $this->ksRegistry = $ksRegistry;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

     
     protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
       
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
        $this->ksRegistry->register('sales_order', $order);
        $this->ksRegistry->register('current_order', $order);
        return $order;
    }
     
     
     public function execute()
    {
        $order = $this->_initOrder();
        $ksSellerId=$this->ksSellerHelper->getKsCustomerId();
        if ($order) {
            try {
                $data = $this->getRequest()->getPost('history');
                if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The comment is missing. Enter and try again.')
                    );
                }
                $order->setStatus($data['status']);
                $notify = $data['is_customer_notified'] ?? false;
                $visible = $data['is_visible_on_front'] ?? false;
                $history = $order->addStatusHistoryComment($data['comment'], $data['status']);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                if($ksSellerId){
                $history->setKsSellerId($ksSellerId);
                }
                $history->save();

                $comment = is_null($data["comment"]) ? "" :trim(strip_tags($data['comment']));

                $order->save();
                /** @var OrderCommentSender $orderCommentSender */
                $orderCommentSender = $this->_objectManager
                    ->create(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);
                $orderCommentSender->send($order, $notify, $comment);
                $response = ['success' => true, 'message' => __('Added in order history.')];
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                error_log(print_r($e->getMessage()));
                $response = ['success' => false, 'message' => __('We cannot add order history.')];
            }
            if (is_array($response)) {
                $resultJson = $this->ksResultJsonFactory->create();
                $resultJson->setData($response);
                return $resultJson;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/listing');
    }
}