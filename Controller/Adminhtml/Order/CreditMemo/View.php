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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\CreditMemo;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemoItem;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * Class View
 */
class View extends \Magento\Backend\App\Action
{
   /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var KsSalesInvoice
     */
    protected $ksSalesCreditMemo;

    /**
     * @var KsSalesInvoiceItem
     */
    protected $ksSalesCreditMemoItem;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $ksCreditMemoRepository;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $ksCreditMemoModel;

    /**
     * @var orderRepository
     */
    protected $ksOrderRepository;

    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $ksCreditmemoRepository;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $ksCreditmemoModel;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param KsSalesCreditMemo $ksSalesCreditMemo
     * @param KsSalesCreditMemoItem $ksSalesCreditMemoItem
     * @param CreditmemoRepositoryInterface $ksCreditmemoRepository
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param \Magento\Sales\Model\Order\Creditmemo $ksCreditmemoModel
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        KsSalesCreditMemo $ksSalesCreditMemo,
        KsSalesCreditMemoItem $ksSalesCreditMemoItem,
        CreditmemoRepositoryInterface $ksCreditmemoRepository,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order\Creditmemo $ksCreditmemoModel,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
    ) {
        $this->ksRegistry = $registry;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
        $this->ksSalesCreditMemoItem = $ksSalesCreditMemoItem;
        $this->ksCreditmemoRepository = $ksCreditmemoRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->ksCreditmemoModel = $ksCreditmemoModel;
        $this->ksOrderRepository = $ksOrderRepository;
        parent::__construct($context);
    }


     /**
     * CreditMemo view page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        
        $ksCreditMemoId = $this->getRequest()->getParam('creditmemo_id');
        $ksSalesCreditMemoDetails = $this->ksSalesCreditMemo->load($ksCreditMemoId);
        /*check count of details*/
        if (!count((array)$ksSalesCreditMemoDetails)) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $ksOrder = $this->ksOrderRepository->get($ksSalesCreditMemoDetails->getKsOrderId());
        $ksCreditMemoRequest = $this->ksSalesCreditMemo->load($ksCreditMemoId);
        $this->ksRegistry->register('current_creditmemo_request', $ksCreditMemoRequest);
        $ksCreditMemoItems = $this->ksSalesCreditMemoItem->getCollection()->addFieldToFilter('ks_parent_id',$ksCreditMemoId);
        $this->ksRegistry->register('current_creditmemo_items',$ksCreditMemoItems); 
        $this->ksRegistry->register('current_order',$ksOrder);        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ksolves_MultivendorMarketplace::all_orders');
        $resultPage->getConfig()->getTitle()->prepend("#".$ksCreditMemoRequest->getKsCreditmemoIncrementId());
        return $resultPage;
    }
}
