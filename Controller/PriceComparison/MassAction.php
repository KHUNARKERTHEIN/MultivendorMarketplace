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
namespace Ksolves\MultivendorMarketplace\Controller\PriceComparison;

use Ksolves\MultivendorMarketplace\Model\KsProductFactory as KsProductCollection;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Framework\Data\Form\FormKey;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Action\Action;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * Product Controller Class
 */
abstract class MassAction extends Action
{

    /**
     * @var \Magento\Framework\App\Request\Http $ksRequest
     */
    protected $ksRequest;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsProductCollection
     */
    protected $ksProductCollection;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $ksFormKey;

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
    private $ksProductRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksProductFactory;

    /**
     * Product constructor.
     * @param Context $ksContext
     * @param Filter $ksFilter
     * @param CollectionFactory $ksCollectionFactory
     * @param KsProductCollection $ksProductCollection
     * @param \Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param FormKey $ksFormKey
     * @param Http $ksRequest
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        Context $ksContext,
        Filter $ksFilter,
        CollectionFactory $ksCollectionFactory,
        KsProductCollection $ksProductCollection,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        FormKey $ksFormKey,
        Http $ksRequest,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksProductCollection = $ksProductCollection;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksFormKey = $ksFormKey;
        $this->ksRequest = $ksRequest;
        $this->ksRequest->setParam('form_key', $this->ksFormKey->getFormKey());
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }
}
