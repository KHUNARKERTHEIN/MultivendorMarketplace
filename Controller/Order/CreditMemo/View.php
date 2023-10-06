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

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemoItem;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;

/**
 * Class View
 */
class View extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
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
     * @var orderRepository
     */
    protected $ksOrderRepository;

    /**
    * @var KsSalesCreditMemo
    */
    protected $ksSalesCreditMemo;

    /**
     * @var KsSalesCreditMemoItem
     */
    protected $ksSalesCreditMemoItem;

    /**
     * @var CreditMemoRepositoryInterface
     */
    protected $ksCreditMemoRepository;

    /**
     * @var \Magento\Sales\Model\Order\CreditMemo
     */
    protected $ksCreditMemoModel;

    /**
     * @var CreditmemoSender
     */
    protected $ksCreditmemoLoader;

    /**
     * @param Context $ksContext
     * @param PageFactory $ksResultPageFactory
     * @param Registry $ksRegistry
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     * @param KsSalesCreditMemo $ksSalesCreditMemo
     * @param KsSalesCreditMemoItem $ksSalesCreditMemoItem
     * @param CreditMemoRepositoryInterface $ksCreditMemoRepository
     * @param \Magento\Sales\Model\Order\CreditMemo $ksCreditMemoModel
     * @param CreditMemoLoader $ksCreditmemoLoader
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory,
        Registry $ksRegistry,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository,
        KsSalesCreditMemo $ksSalesCreditMemo,
        KsSalesCreditMemoItem $ksSalesCreditMemoItem,
        CreditMemoRepositoryInterface $ksCreditMemoRepository,
        \Magento\Sales\Model\Order\Creditmemo $ksCreditMemoModel,
        CreditMemoLoader $ksCreditmemoLoader
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksRegistry = $ksRegistry;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
        $this->ksSalesCreditMemoItem = $ksSalesCreditMemoItem;
        $this->ksCreditMemoRepository = $ksCreditMemoRepository;
        $this->ksCreditMemoModel = $ksCreditMemoModel;
        $this->ksCreditmemoLoader=$ksCreditmemoLoader;
        parent::__construct($ksContext);
    }

    /**
     * Creditmemo information page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksCreditMemoId = $this->getRequest()->getParam('creditmemo_id');
        $ksCreditMemoRequest = $this->ksSalesCreditMemo->load($ksCreditMemoId);
        $ksOrder = $this->ksOrderRepository->get($ksCreditMemoRequest->getKsOrderId());
        $this->ksRegistry->register('current_creditmemo_request', $ksCreditMemoRequest);
        $ksCreditMemoItems = $this->ksSalesCreditMemoItem->getCollection()->addFieldToFilter('ks_parent_id', $ksCreditMemoId);
        if ($ksCreditMemoRequest->getKsApprovalStatus()==$this->ksSalesCreditMemo::KS_STATUS_APPROVED) {
            $this->ksRegistry->register('current_creditmemo_id', $this->ksCreditMemoModel->load($ksCreditMemoRequest->getKsCreditmemoIncrementId(), 'increment_id')->getId());
        }
        $this->ksRegistry->register('current_creditmemo_items', $ksCreditMemoItems);
        $this->ksRegistry->register('current_order', $ksOrder);
       
        $resultPage = $this->ksResultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('View Memo'));
        return $resultPage;
    }
}
