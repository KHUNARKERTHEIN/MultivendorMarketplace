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
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Sales\Model\Order\Address\Renderer;

/**
 * SendEmail Controller
 */
class SendEmail extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var orderRepository
     */
    protected $ksOrderRepository;

    /**
     * @var PaymentHelper
     */
    protected $ksPaymentHelper;

    /**
     * @var KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var Renderer
     */
    protected $ksAddksAddressRenderer;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     * @param KsSellerHelper $ksSellerHelper
     * @param PaymentHelper $ksPaymentHelper
     * @param KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param Renderer $ksAddressRenderer
     */
    public function __construct( 
        \Magento\Framework\App\Action\Context $ksContext,      
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository,
        KsSellerHelper $ksSellerHelper,
        PaymentHelper $ksPaymentHelper,
        KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        Renderer $ksAddressRenderer
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksPaymentHelper = $ksPaymentHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksAddksAddressRenderer = $ksAddressRenderer;
        parent::__construct($ksContext);
    }

     /**
     * Initialize order model instance
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function ksInitOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $ksIsSellerOrder = $this->ksSellerHelper->KsIsSellerOrder($id);
        if(!$ksIsSellerOrder)
                return false;  
        try {
            $ksOrder = $this->ksOrderRepository->get($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        return $ksOrder;
    } 

    /**
     * Get payment info block as html
     *
     * @param $ksOrder
     * @return string
     */
    protected function getKsPaymentHtml($ksOrder)
    {
        return $this->ksPaymentHelper->getInfoBlockHtml(
            $ksOrder->getPayment(),
            $this->ksDataHelper->getKsCurrentStoreView()
        );
    }     
    
    /**
     * Render shipping address into html.
     *
     * @param Order $ksOrder
     * @return string|null
     */
    protected function getFormattedShippingAddress($ksOrder)
    {
        return $ksOrder->getIsVirtual()
            ? null
            : $this->ksAddksAddressRenderer->format($ksOrder->getShippingAddress(), 'html');
    }

    /**
     * Render billing address into html.
     *
     * @param Order $ksOrder
     * @return string|null
     */
    protected function getFormattedBillingAddress($ksOrder)
    {
        return $this->ksAddksAddressRenderer->format($ksOrder->getBillingAddress(), 'html');
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
            $ksOrder = $this->ksInitOrder();
            $ksSender = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_sales/ks_order_settings/ks_sender_of_email',
                $this->ksDataHelper->getKsCurrentStoreView()
            );
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
            $emailVariables = [
                'order' => $ksOrder,
                'order_id' => $ksOrder->getId(),
                'billing' => $ksOrder->getBillingAddress(),
                'payment_html' => $this->getKsPaymentHtml($ksOrder),
                'store' => $ksOrder->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($ksOrder),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($ksOrder),
                'created_at_formatted' => $ksOrder->getCreatedAtFormatted(2),
                'order_data' => [
                    'customer_name' => $ksOrder->getCustomerName(),
                    'is_not_virtual' => $ksOrder->getIsNotVirtual(),
                    'email_customer_note' => $ksOrder->getEmailCustomerNote(),
                    'frontend_status_label' => $ksOrder->getFrontendStatusLabel()
                ]
            ];
            $this->ksEmailHelper->ksSellerOrderMail($emailVariables,$ksSenderInfo,$ksOrder->getCustomerName(),$ksOrder->getCustomerEmail());
            $this->messageManager->addSuccess(__('You sent the order email.'));
            return $this->resultRedirectFactory->create()->setUrl(
                $this->_redirect->getRefererUrl()
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/create',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}