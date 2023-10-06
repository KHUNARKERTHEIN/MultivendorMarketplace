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

namespace Ksolves\MultivendorMarketplace\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Ksolves\MultivendorMarketplace\Controller\Product
 */
class MassDelete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $ksFilter;

    /**
     * @var ProductRepositoryInterface|null
     */
    private $productRepository;

    /**
     * @var Magento\Catalog\Model\Product\Action
     */
    protected $ksProductAction;

    /**
     * @var KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * @var Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $ksPriceIndexerProcessor;

    /**
     * @var Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $ksSetFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $ksFormKey;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $ksRequest;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param \Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param \Magento\Ui\Component\MassAction\Filter $ksFilter
     * @param \Magento\Framework\Data\Form\FormKey $ksFormKey
     * @param \Magento\Framework\App\Request\Http $ksRequest
     * @param ProductRepositoryInterface $productRepository
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksSellerProductFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $ksPriceIndexerProcessor
     * @param \Magento\Catalog\Model\Product\Action $ksProductAction
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Magento\Ui\Component\MassAction\Filter $ksFilter,
        \Magento\Framework\Data\Form\FormKey $ksFormKey,
        \Magento\Framework\App\Request\Http $ksRequest,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksSellerProductFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Magento\Catalog\Model\Product\Action $ksProductAction,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $ksPriceIndexerProcessor,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $ksSetFactory,
        ProductRepositoryInterface $productRepository = null
    ) {
        $this->ksProductFactory = $ksProductFactory;
        $this->ksFilter = $ksFilter;
        $this->ksFormKey = $ksFormKey;
        $this->ksRequest = $ksRequest;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksSellerProductFactory  = $ksSellerProductFactory;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksProductAction = $ksProductAction;
        $this->ksPriceIndexerProcessor = $ksPriceIndexerProcessor;
        $this->ksRequest->setParam('form_key', $this->ksFormKey->getFormKey());
        $this->productRepository = $productRepository;
        $this->ksSetFactory = $ksSetFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                //get data
                $ksProductCollection = $this->ksFilter->getCollection($this->ksProductFactory->create()->getCollection());

                //get size
                $ksCollectionSize = $ksProductCollection->getSize();
                $ksProductData = $ksProductCollection->getData();

                for ($i=0; $i<$ksCollectionSize; $i++) {
                    $this->ksProductFactory->create()
                        ->load($ksProductData[$i]['entity_id'])
                        ->delete();
                }

                $this->messageManager->addSuccess(__('A total of %1 product(s) has been deleted.', $ksCollectionSize));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
