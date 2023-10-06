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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesOrder\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassUnHold
 */
class MassUnHold extends \Magento\Backend\App\Action
{
    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $ksOrderRepository;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;


    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderRepository $ksOrderRepository
     * @param OrderManagementInterface|null $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepository $ksOrderRepository,
        OrderManagementInterface $orderManagement = null
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Sales\Api\OrderManagementInterface::class
        );
        $this->ksOrderRepository = $ksOrderRepository;
        parent::__construct($context);
    }

   
    public function execute()
    {
        $ksCollection = $this->filter->getCollection($this->collectionFactory->create());

        $ksCollectionSize = $ksCollection->getSize();
        $ksUnholdCount = 0;
        
        foreach ($ksCollection as $order) {
            $ksSalesOrder = $this->ksOrderRepository->get($order->getKsOrderId());
            if ($ksSalesOrder->canUnhold()) {
                $this->orderManagement->unHold($order->getKsOrderId());
                $ksUnholdCount++;
            }
        }

        if (!$ksUnholdCount) {
            $this->messageManager->addErrorMessage(__('%1 order(s) were not released from on hold status.', $ksCollectionSize));
        } else {
            if ($ksUnholdCount) {
                $this->messageManager->addSuccess(__('%1 order(s) have been released from on hold status.', $ksCollectionSize));
            } else {
                $this->messageManager->addErrorMessage(__('No order(s) were released from on hold status.', $ksCollectionSize));
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $ksResultRedirect->setPath('*/*/');
    }
}
