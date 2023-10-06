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

use Magento\Framework\Controller\ResultFactory;

/**
 * Class PdfDocumentsMassAction
 */
abstract class PdfDocumentsMassAction extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->getOrderCollection()->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    /**
     * Get Order Collection Factory
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     * @deprecated 100.1.3
     */
    private function getOrderCollection()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class
            );
        }
        return $this->orderCollectionFactory;
    }
}
