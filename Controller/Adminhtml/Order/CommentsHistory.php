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

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Controller\Adminhtml\Order as OrderAction;

/**
 * CommentsHistory Controller
 *
 */
class CommentsHistory extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $ksLayoutFactory;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $ksTranslateInline;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $ksResultRawFactory;

    /**
     * @param Action\Context $ksContext
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Translate\InlineInterface $ksTranslateInline
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $ksResultRawFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param \Magento\Framework\View\LayoutFactory $ksLayoutFactory
     *
     */
    public function __construct(
        Action\Context $ksContext,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $ksTranslateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $ksResultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        \Magento\Framework\View\LayoutFactory $ksLayoutFactory
    ) {
        $this->ksTranslateInline = $ksTranslateInline;
        $this->ksResultRawFactory = $ksResultRawFactory;
        $this->ksLayoutFactory = $ksLayoutFactory;
        parent::__construct(
            $ksContext,
            $coreRegistry,
            $fileFactory,
            $ksTranslateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $ksResultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
    }

    /**
     * Generate order history for ajax request
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $this->_initOrder();
        $layout = $this->ksLayoutFactory->create();
        $html = $layout->createBlock(\Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\View\Tab\History::class)
            ->toHtml();
        $this->ksTranslateInline->processResponseBody($html);
        // * @var \Magento\Framework\Controller\Result\Raw $resultRaw 
        $resultRaw = $this->ksResultRawFactory->create();
        $resultRaw->setContents($html);
        return $resultRaw;
    }
}
