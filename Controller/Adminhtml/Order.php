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
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;

/**
 * Adminhtml sales orders controller
 */
abstract class Order extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ksolves_MultivendorMarketplace::manage_sales';

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var string[]
     */
    protected $_publicActions = ['view', 'index'];

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $ksFileFactory;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $ksTranslateInline;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $ksResultJsonFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $ksResultLayoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $ksResultRawFactory;

    /**
     * @var OrderManagementInterface
     */
    protected $ksOrderManagement;

    /**
     * @var OrderRepositoryInterface
     */
    protected $ksOrderRepository;

    /**
     * @var LoggerInterface
     */
    protected $ksLogger;

    /**
     * @param Action\Context $ksContext
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $ksFileFactory
     * @param \Magento\Framework\Translate\InlineInterface $ksTranslateInline
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $ksResultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $ksResultRawFactory
     * @param OrderManagementInterface $ksOrderManagement
     * @param OrderRepositoryInterface $ksOrderRepository
     * @param LoggerInterface $ksLogger
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        Action\Context $ksContext,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $ksFileFactory,
        \Magento\Framework\Translate\InlineInterface $ksTranslateInline,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $ksResultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $ksResultRawFactory,
        OrderManagementInterface $ksOrderManagement,
        OrderRepositoryInterface $ksOrderRepository,
        LoggerInterface $ksLogger
    ) {
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksFileFactory = $ksFileFactory;
        $this->ksTranslateInline = $ksTranslateInline;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
        $this->ksResultRawFactory = $ksResultRawFactory;
        $this->ksOrderManagement = $ksOrderManagement;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksLogger = $ksLogger;
        parent::__construct($ksContext);
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->ksResultPageFactory->create();
        $resultPage->setActiveMenu('Ksolves_MultivendorMarketplace::manage_sales');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Orders'), __('Orders'));
        return $resultPage;
    }

    /**
     * Initialize order model instance
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('ks_order_id');
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
        $this->ksCoreRegistry->register('sales_order', $order);
        $this->ksCoreRegistry->register('current_order', $order);
        return $order;
    }

    /**
     * @return bool
     */
    protected function isValidPostRequest()
    {
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        return ($formKeyIsValid && $isPost);
    }
}
