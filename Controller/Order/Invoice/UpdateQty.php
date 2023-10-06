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
     * @var InvoiceService
     */
    private $ksInvoiceService;

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
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var KsSalesOrderItem
     */
    protected $ksOrderItemModel;

    /**
     * KsSubReasons Constructor
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param Registry $ksRegistry
     * @param InvoiceService $ksInvoiceService
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param KsProductHelper $ksProductHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesOrderItem $ksOrderItemModel
     * @param OrderRepositoryInterface $ksOrderRepository = null
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        Registry $ksRegistry,
        InvoiceService $ksInvoiceService,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        KsProductHelper $ksProductHelper,
        KsOrderHelper $ksOrderHelper,
        KsSalesOrderItem $ksOrderItemModel,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksInvoiceService = $ksInvoiceService;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksJsonHelper = $ksJsonHelper;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksOrderItemModel = $ksOrderItemModel;
        parent::__construct($ksContext);
    }

    /**
     * Return updated quantity invoice block
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        $ksQtys = $this->getRequest()->getParam('qtys');
        $ksItemIds = $this->getRequest()->getParam('itemIds');
        $ksItems = [];
        /*create items array by supplied id and qty arrays*/
        foreach ($ksItemIds as $ksIndex => $ksItemId) {
            $ksItems[$ksItemId] = $ksQtys[$ksIndex];
            $ksItems = array_filter($ksItems, 'strlen');
        }
        
        $ksOrderId = $this->getRequest()->getParam('order_id');
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $ksOrder = $this->ksOrderRepository->get($ksOrderId);
            $this->ksRegistry->register('current_order', $ksOrder);
            if (!$ksOrder->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }

            foreach ($ksOrder->getAllItems() as $item) {
                if (!$item->getParentItemId() && isset($ksItems[$item->getId()])) {
                    $validQty = $this->ksOrderItemModel->loadByKsOrderItemId($item->getId())->getKsQtyToInvoice();
                    if ($validQty  < $ksItems[$item->getId()]) {
                        return $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode(["error"=>"We found an invalid quantity to invoice item ".$item->getName()]));
                    }
                }
                if (!$this->ksProductHelper->isKsSellerProduct($item->getProductId())) {
                    unset($ksItems[$item->getId()]);
                }
            }
            $invoice = $this->ksInvoiceService->prepareInvoice($ksOrder, $ksItems);
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The invoice can't be created without products. Add products and try again.")
                );
            }
            $this->ksRegistry->unregister('current_invoice');
            $this->ksRegistry->register('current_invoice', $invoice);
            $html = $this->ksResultPageFactory->create()->getLayout()
            ->createBlock('Ksolves\MultivendorMarketplace\Block\Order\Invoice\Create\KsItems')
            ->setTemplate('Ksolves_MultivendorMarketplace::order/invoice/create/items.phtml')
            ->toHtml();
            return $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode($html));
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode(['error'=>$exception->getMessage()]));
        } catch (\Exception $exception) {
            $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode(['error' => __('Cannot update item quantity.')]));
        }
    }
}
