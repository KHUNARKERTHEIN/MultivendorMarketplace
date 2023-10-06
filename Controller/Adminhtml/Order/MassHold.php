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
 * Class MassHold
 */
class MassHold extends \Magento\Backend\App\Action
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
        $ksHoldCount = 0;

        foreach ($ksCollection as $order) {
            $ksSalesOrder = $this->ksOrderRepository->get($order->getKsOrderId());
            if ($ksSalesOrder->canHold()) {
                $this->orderManagement->hold($order->getKsOrderId());
                $ksHoldCount++;
            }
        }

        if (!$ksCollectionSize) {
            $this->messageManager->addErrorMessage(__('%1 order(s) were not put on hold.'));
        } else {
            $ksCollection = $this->filter->getCollection($this->collectionFactory->create())->addFieldToFilter('id', ['eq' => 1]);
            if ($ksHoldCount) {
                $this->messageManager->addSuccess(__('You have put %1 order(s) on hold.', $ksHoldCount));
            } else {
                $this->messageManager->addErrorMessage(__('No order(s) were put on hold.'));
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $ksResultRedirect->setPath('*/*/');
    }
}
