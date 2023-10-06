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

use Ksolves\MultivendorMarketplace\Model\KsProductFactory as KsProductCollection;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\Component\MassAction\Filter;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory as KsProductCollectionFactory;

/**
 * Product Controller Class
 */
abstract class Product extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var KsProductCollection
     */
    protected $ksProductCollection;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var Filter
     */
    protected $ksFilter;

    /**
     * @var CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Catalog\Model\ProductCategoryList
     */
    protected $ksCategoryList;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Processor
     */
    protected $ksFullTextProcessor;

    /**
     * @var \agento\Catalog\Model\Indexer\Product\Category\Processor
     */
    protected $ksProductCategoryProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $ksPriceIndexerProcessor;

    /**
     * @var KsProductCollectionFactory
     */
    protected $ksProductCollectionFactory;

    /**
     * @var Magento\Catalog\Model\Product\Action
     */
    protected $ksProductAction;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $ksCategoryFactory;

    /**
     * Product constructor.
     *
     * @param Context $ksContext
     * @param Builder $ksProductBuilder
     * @param Filter $ksFilter
     * @param CollectionFactory $ksCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param KsProductCollection $ksProductCollection
     * @param DataPersistorInterface $ksDataPersistor
     * @param \Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory
     * @param \Magento\Catalog\Model\ProductCategoryList $ksCategoryList
     * @param \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper
     * @param \Magento\CatalogSearch\Model\Indexer\Fulltext\Processor $ksFullTextProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Category\Processor $ksProductCategoryProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $ksPriceIndexerProcessor
     * @param \Magento\Catalog\Model\Product\Action $ksProductAction
     * @param KsProductCollectionFactory $ksProductCollectionFactory
     * @param ProductRepositoryInterface|null $ksProductRepository
     */
    public function __construct(
        Context $ksContext,
        Builder $ksProductBuilder,
        Filter $ksFilter,
        CollectionFactory $ksCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        KsProductCollection $ksProductCollection,
        DataPersistorInterface $ksDataPersistor,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory,
        \Magento\Catalog\Model\ProductCategoryList $ksCategoryList,
        \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper,
        \Magento\CatalogSearch\Model\Indexer\Fulltext\Processor $ksFullTextProcessor,
        \Magento\Catalog\Model\Indexer\Product\Category\Processor $ksProductCategoryProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $ksPriceIndexerProcessor,
        \Magento\Catalog\Model\Product\Action $ksProductAction,
        KsProductCollectionFactory $ksProductCollectionFactory,
        ProductRepositoryInterface $ksProductRepository = null
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksProductRepository = $ksProductRepository ?:
            \Magento\Framework\App\ObjectManager::getInstance()->create(ProductRepositoryInterface::class);
        $this->ksDate = $ksDate;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksProductCollection = $ksProductCollection;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksCategoryFactory = $ksCategoryFactory;
        $this->ksCategoryList = $ksCategoryList;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksFullTextProcessor = $ksFullTextProcessor;
        $this->ksProductCategoryProcessor = $ksProductCategoryProcessor;
        $this->ksPriceIndexerProcessor = $ksPriceIndexerProcessor;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksProductAction = $ksProductAction;
        parent::__construct($ksContext, $ksProductBuilder);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::manage_products');
    }
}
