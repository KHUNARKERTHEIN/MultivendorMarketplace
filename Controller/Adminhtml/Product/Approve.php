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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Approve
 * @package Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product
 */
class Approve extends \Magento\Backend\App\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Processor
     */
    protected $ksFullTextProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Category\Processor
     */
    protected $ksProductCategoryProcessor;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $ksPriceIndexerProcessor;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * Approve constructor
     *
     * @param Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $ksProductRepository
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Magento\CatalogSearch\Model\Indexer\Fulltext\Processor $ksFullTextProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Category\Processor $ksProductCategoryProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $ksPriceIndexerProcessor
     */
    public function __construct(
        Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Magento\Catalog\Api\ProductRepositoryInterface $ksProductRepository,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Magento\CatalogSearch\Model\Indexer\Fulltext\Processor $ksFullTextProcessor,
        \Magento\Catalog\Model\Indexer\Product\Category\Processor $ksProductCategoryProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $ksPriceIndexerProcessor
    ) {
        $this->ksProductFactory = $ksProductFactory;
        $this->ksDate = $ksDate;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFullTextProcessor = $ksFullTextProcessor;
        $this->ksProductCategoryProcessor = $ksProductCategoryProcessor;
        $this->ksPriceIndexerProcessor  = $ksPriceIndexerProcessor;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksProductId = $this->getRequest()->getParam('id');
        $ksApprovalStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED;

        try {
            //check product Id
            if ($ksProductId) {
                // load model
                $ksModel = $this->ksProductFactory->create()->load($ksProductId, 'ks_product_id');
                $ksStatus = $ksModel->getKsProductApprovalStatus();
                $ksId = [];
                $ksProId = [];
                $ksProId[] = $ksProductId;

                //check model
                if ($ksModel) {
                    $ksModel->setKsProductApprovalStatus($ksApprovalStatus);
                    $ksModel->setKsEditMode(1);
                    $ksModel->setKsRejectionReason("");
                    $ksModel->setKsUpdatedAt($this->ksDate->gmtDate());
                    $ksSellerId = $ksModel->getKsSellerId();
                    $ksId[] = $ksSellerId;
                    $ksModel->save();
                    $ksProductData = $this->ksProductRepository->getById(
                        $ksProductId,
                        true,
                        $this->getRequest()->getParam('store', 0)
                    );
                    $ksStoreId = $ksProductData->getStoreId();

                    //reindex data to filter approve seller product
                    $this->ksFullTextProcessor->reindexList(array($ksProductId));
                    $this->ksProductCategoryProcessor->reindexList(array($ksProductId));
                    $this->ksPriceIndexerProcessor->reindexList(array($ksProductId));

                    $this->messageManager->addSuccessMessage(__("A product has been approved successfully."));

                    $this->ksEmailHelper->ksSendEmailProductApprove($ksId, $ksProId);
                } else {
                    $this->messageManager->addErrorMessage(__("Something went wrong while approving product."));
                }
            } else {
                $this->messageManager->addErrorMessage(__("Something went wrong while approving product."));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
