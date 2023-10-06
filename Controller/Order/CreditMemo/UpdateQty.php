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

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem;

/**
 * UpdateQty Controller class
 */
class UpdateQty extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $ksOrderRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $ksCreditmemoLoader;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var KsSalesOrderItem
     */
    protected $ksOrderItemModel;

    /**
     * UpdateQty Constructor
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param Registry $ksRegistry
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param KsProductHelper $ksProductHelper
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $ksCreditmemoLoader
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesOrderItem $ksOrderItemModel
     * @param OrderRepositoryInterface $ksOrderRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        Registry $ksRegistry,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        KsProductHelper $ksProductHelper,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $ksCreditmemoLoader,
        KsOrderHelper $ksOrderHelper,
        KsSalesOrderItem $ksOrderItemModel,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksJsonHelper = $ksJsonHelper;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksCreditmemoLoader = $ksCreditmemoLoader;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksOrderItemModel = $ksOrderItemModel;
        parent::__construct($ksContext);
    }

    /**
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        $ksMemoItems =$this->getRequest()->getParam('creditmemo');
        $ksOrderId = $ksMemoItems['order_id'];
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $ksOrder = $this->ksOrderRepository->get($ksOrderId);
            $this->ksRegistry->register('current_order', $ksOrder);
            if (!$ksOrder->canCreditmemo()) {
                $this->messageManager->addErrorMessage(__("The order does not allow a memo to be created."));
                return $this->_redirectToOrder($ksOrderId);
            }
            $ksTotalQty = 0;
            foreach ($ksMemoItems['items'] as $ksItem) {
                $ksTotalQty+=$ksItem['qty'];
            }
            if (!$ksTotalQty>0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The creditmemo can't be created without products. Add products and try again.")
                );
            }
            $ksMemoItems['shipping_amount'] = 0;
            $this->ksRegistry->unregister('current_creditmemo');
            $this->ksCreditmemoLoader->setOrderId($ksOrderId);
            $this->ksCreditmemoLoader->setCreditmemo($ksMemoItems);
            $creditmemo=$this->ksCreditmemoLoader->load();
            $html = $this->ksResultPageFactory->create()->getLayout()
            ->createBlock('Ksolves\MultivendorMarketplace\Block\Order\CreditMemo\Create\KsItems')
            ->setTemplate('Ksolves_MultivendorMarketplace::order/creditmemo/create/items.phtml')
            ->toHtml();
            return $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode($html));
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode(['error'=>$exception->getMessage()]));
        } catch (\Exception $exception) {
            $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode(['error'=>$exception->getMessage()]));
        }
    }
}
