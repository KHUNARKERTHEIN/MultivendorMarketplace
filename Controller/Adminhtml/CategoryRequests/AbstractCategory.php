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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CategoryRequests;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as KsSellerCategoriesCollection;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory as KsCategoryRequestsCollection;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests;

/**
 * abstract class AbstractCategory
 */
abstract class AbstractCategory extends Action
{
    /**
     * @var Filter
     */
    protected $ksFilter;

    /**
     * @var KsCategoryRequestsFactory
     */
    protected $ksCategoryRequestsFactory;

    /**
     * @var KsSellerCategoriesFactory
     */
    protected $ksSellerCategoriesFactory;
    
    /**
     * @var KsSellerCategoriesCollection
     */
    protected $ksSellerCategoriesCollection;

    /**
     * @var KsCategoryRequestsCollection
     */
    protected $ksCategoryRequestsCollection;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $ksCustomerFactory;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $KsDataHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var  KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @param Context $ksContext
     * @param Filter $ksFilter
     * @param KsCategoryRequestsFactory $ksCategoryRequestsFactory
     * @param KsSellerCategoriesFactory $ksSellerCategoriesFactory
     * @param KsSellerCategoriesCollection $ksSellerCategoriesCollection
     * @param KsCategoryRequestsCollection $ksCategoryRequestsCollection
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Customer\Model\CustomerFactory $ksCustomerFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param KsCategoryRequests $ksCategoryHelper
     */
    public function __construct(
        Context $ksContext,
        Filter $ksFilter,
        KsCategoryRequestsFactory $ksCategoryRequestsFactory,
        KsSellerCategoriesFactory $ksSellerCategoriesFactory,
        KsSellerCategoriesCollection $ksSellerCategoriesCollection,
        KsCategoryRequestsCollection $ksCategoryRequestsCollection,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Customer\Model\CustomerFactory $ksCustomerFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        KsCategoryRequests $ksCategoryHelper
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksCategoryRequestsFactory = $ksCategoryRequestsFactory;
        $this->ksSellerCategoriesFactory = $ksSellerCategoriesFactory;
        $this->ksSellerCategoriesCollection = $ksSellerCategoriesCollection;
        $this->ksCategoryRequestsCollection = $ksCategoryRequestsCollection;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCustomerFactory = $ksCustomerFactory;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksCategoryHelper = $ksCategoryHelper;
        parent::__construct($ksContext);
    }
}
