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

namespace Ksolves\MultivendorMarketplace\Plugin\Product;

use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Action;
use Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class KsMassStatusEnable
 * @package Ksolves\MultivendorMarketplace\Model\Plugin
 */
class KsMassStatus
{
    /**
     * @var KsProductTypeHelper
     */
    protected $ksProductHelper;

    /**
     * @var Filter
     */
    protected $ksFilter;

    /**
     * @var CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var Action
     */
    protected $ksProductAction;

    /**
     * @var Processor
     */
    protected $ksPriceIndexerProcessor;

    /**
     * @var ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var ResultFactory
     */
    protected $ksResultFactory;

    /**
     * @var KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @param KsProductTypeHelper $ksProduct
     * @param CollectionFactory $ksCollectionFactory
     * @param KsProductTypeHelper $ksProductTypeHelper
     * @param Action $ksProductAction
     * @param Processor $ksPriceIndexerProcessor
     * @param ManagerInterface $ksMessageManager
     * @param ResultFactory $ksResultFactory
     */
    public function __construct(
        Filter $ksFilter,
        CollectionFactory $ksCollectionFactory,
        KsProductTypeHelper $ksProductTypeHelper,
        Action $ksProductAction,
        Processor $ksPriceIndexerProcessor,
        ManagerInterface $ksMessageManager,
        ResultFactory $ksResultFactory
    ) {
        $this->ksCollectionFactory  = $ksCollectionFactory;
        $this->ksFilter             = $ksFilter;
        $this->ksProductTypeHelper  = $ksProductTypeHelper;
        $this->ksProductAction      = $ksProductAction;
        $this->ksPriceIndexerProcessor  = $ksPriceIndexerProcessor;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksResultFactory = $ksResultFactory;
    }

    /**
     * Restrict to enable by not allowed type or rejected product
     * @param MassStatus $ksSubject
     */
    public function aroundExecute(
        \Magento\Catalog\Controller\Adminhtml\Product\MassStatus $subject,
        callable $proceed
    ) {
        $ksCollection = $this->ksFilter->getCollection($this->ksCollectionFactory->create());
        $ksProductIds = $ksCollection->getAllIds();
        $requestStoreId = $storeId = $subject->getRequest()->getParam('store', null);
        $filterRequest = $subject->getRequest()->getParam('filters', null);
        $ksStatus = (int) $subject->getRequest()->getParam('status');

        //filter productids by not allowed type or rejected product
        $ksProductIds = $this->ksProductTypeHelper->KsRestrictEnableProductByNotAllowedType($ksProductIds, $ksStatus);

        if (null === $storeId && null !== $filterRequest) {
            $storeId = (isset($filterRequest['store_id'])) ? (int) $filterRequest['store_id'] : 0;
        }

        try {
            $subject->_validateMassStatus($ksProductIds, $ksStatus);
            $this->ksProductAction->updateAttributes($ksProductIds, ['status' => $ksStatus], (int) $storeId);
            $this->ksMessageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', count($ksProductIds))
            );
            $this->ksPriceIndexerProcessor->reindexList($ksProductIds);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->ksMessageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->ksMessageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the product(s) status.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->ksResultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('catalog/*/', ['store' => $requestStoreId]);
    }
}
