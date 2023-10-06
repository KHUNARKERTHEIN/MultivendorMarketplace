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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Config\Source\Product\Options\TierPrice;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Directory\Helper\Data as KsDirectoryHelper;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection as KsEavGroupCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration\GetData as KsGetDataModel;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Msrp\Model\Config as MsrpConfig;
use Magento\Store\Model\StoreManagerInterface;

/**
 * KsAdvancePrice block class
 */
class KsAdvancePrice extends \Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\KsAttribute
{
    /**
     * @var KsEavGroupCollection
     */
    protected $ksEavGroupCollection;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $ksSearchCriteriaBuilder;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $ksAttributeRepository;

    /**
     * @var MsrpConfig
     */
    protected $ksMsrpConfig;

    /**
     * Websites cache
     *
     * @var array
     */
    protected $ksWebsites;

    /**
     * Customer groups cache
     *
     * @var array
     */
    protected $ksCustomerGroups;

    /**
     * Catalog data
     *
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var Manager
     */
    protected $ksModuleManager;

    /**
     * @var GroupManagementInterface
     */
    protected $ksGroupManagement;

    /**
     * @var KsDirectoryHelper
     */
    protected $ksDirectoryHelper;

    /**
     * @var TierPrice
     */
    protected $ksTierProductPriceOptions;

    /**
     * @var GroupRepositoryInterface
     */
    protected $ksGroupRepository;

    /**
     * @param Context $ksContext
     * @param StoreManagerInterface $ksStoreManager
     * @param CurrencyFactory $ksCurrencyFactory
     * @param MsrpConfig $ksMsrpConfig
     * @param KsEavGroupCollection $ksEavGroupCollection
     * @param SearchCriteriaBuilder $ksSearchCriteriaBuilder
     * @param ProductAttributeRepositoryInterface $ksAttributeRepository
     * @param Manager $ksModuleManager
     * @param GroupRepositoryInterface $ksGroupRepository
     * @param GroupManagementInterface $ksGroupManagement
     * @param KsDirectoryHelper $ksDirectoryHelper
     * @param TierPrice $ksTierProductPriceOptions,
     * @param Registry $ksRegistry
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param GetSalableQuantityDataBySku
     * @param array $ksData  $ksGetScalableQuantity
     * @param ScopeOverriddenValue $ksScopeOverriddenValue
     */
    public function __construct(
        Context $ksContext,
        StoreManagerInterface $ksStoreManager,
        CurrencyFactory $ksCurrencyFactory,
        MsrpConfig $ksMsrpConfig,
        KsEavGroupCollection $ksEavGroupCollection,
        SearchCriteriaBuilder $ksSearchCriteriaBuilder,
        ProductAttributeRepositoryInterface $ksAttributeRepository,
        Manager $ksModuleManager,
        GroupRepositoryInterface $ksGroupRepository,
        GroupManagementInterface $ksGroupManagement,
        KsDirectoryHelper $ksDirectoryHelper,
        TierPrice $ksTierProductPriceOptions,
        Registry $ksRegistry,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        GetSalableQuantityDataBySku $ksGetScalableQuantity,
        ScopeOverriddenValue $ksScopeOverriddenValue,
        GetSourceItemsDataBySku $ksGetSourceItemsDataBySku,
        KsGetDataModel $ksGetDataResourceModel = null,
        array $ksData = []
    ) {
        $this->ksMsrpConfig = $ksMsrpConfig;
        $this->ksSearchCriteriaBuilder = $ksSearchCriteriaBuilder;
        $this->ksAttributeRepository = $ksAttributeRepository;
        $this->ksEavGroupCollection = $ksEavGroupCollection;
        $this->ksModuleManager = $ksModuleManager;
        $this->ksGroupRepository = $ksGroupRepository;
        $this->ksGroupManagement = $ksGroupManagement;
        $this->ksDirectoryHelper = $ksDirectoryHelper;
        $this->ksTierProductPriceOptions = $ksTierProductPriceOptions;
        parent::__construct(
            $ksContext,
            $ksStoreManager,
            $ksCurrencyFactory,
            $ksRegistry,
            $ksDataHelper,
            $ksProductHelper,
            $ksDataPersistor,
            $ksGetScalableQuantity,
            $ksScopeOverriddenValue,
            $ksGetSourceItemsDataBySku,
            $ksGetDataResourceModel,
            $ksData
        );
    }

    /**
     * Check MsrpPrice Enable
     *
     * @return boolean
     */
    public function getKsEnableMsrpPrice()
    {
        return $this->ksMsrpConfig->isEnabled();
    }

    /**
     * Get Attribute from Advance Pricing group
     *
     * @return object
     */
    public function getKsAdvancePriceAttribute()
    {
        $ksGroupCollection = $this->ksEavGroupCollection;
        $ksGroupCollection->addFieldToFilter('attribute_group_code', 'advanced-pricing');

        $ksAttributeGroupId = $ksGroupCollection->getFirstItem()->getId();

        $ksSearchCriteria = $this->ksSearchCriteriaBuilder
                    ->addFilter(\Magento\Eav\Api\Data\AttributeGroupInterface::GROUP_ID, $ksAttributeGroupId)
                    ->addFilter(\Magento\Catalog\Api\Data\ProductAttributeInterface::IS_VISIBLE, 1)
                    ->create();

        $ksGroupAttributes = $this->ksAttributeRepository->getList($ksSearchCriteria)->getItems();

        $ksProductType = $this->getKsProduct()->getTypeId();

        //fetch all attributes from advance price group
        $ksAttributes = [];
        foreach ($ksGroupAttributes as $ksAttribute) {
            if (!$this->getKsEnableMsrpPrice()) {
                if ($ksAttribute->getAttributeCode()=='msrp' ||
                    $ksAttribute->getAttributeCode()=='msrp_display_actual_price_type') {
                    continue;
                }
            }

            $ksApplyTo = $ksAttribute->getApplyTo();
            $ksIsRelated = !$ksApplyTo || in_array($ksProductType, $ksApplyTo);
            if ($ksIsRelated) {
                $ksAttributes[] = $ksAttribute;
            }
        }
        return $ksAttributes;
    }

    /**
     * Check group price attribute scope is global
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $ksAttribute = $objectManager->get('\Magento\Eav\Model\Config')
                               ->getAttribute('catalog_product', 'tier_price');

        return $ksAttribute->isScopeGlobal();
    }

    /**
     * Retrieve list of initial customer groups
     *
     * @return array
     */
    protected function _getKsInitialCustomerGroups()
    {
        return [$this->ksGroupManagement->getAllCustomersGroup()->getId() => __('ALL GROUPS')];
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     */
    public function getKsDefaultWebsite()
    {
        if ($this->isKsShowWebsiteColumn() && !$this->isKsAllowChangeWebsite()) {
            return $this->ksStoreManager->getStore($this->getKsProduct()->getStoreId())->getWebsiteId();
        }
        return 0;
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     */
    public function getKsDefaultCustomerGroup()
    {
        return $this->ksGroupManagement->getAllCustomersGroup()->getId();
    }

    /**
     * Retrieve allowed for edit websites
     *
     * @return array
     */
    public function getKsWebsites()
    {
        if ($this->ksWebsites !== null) {
            return $this->ksWebsites;
        }

        $this->ksWebsites = [
            0 => ['name' => __('All Websites'), 'currency' => $this->ksDirectoryHelper->getBaseCurrencyCode()]
        ];

        if (!$this->isScopeGlobal() && $this->getKsProduct()->getStoreId()) {
            /** @var $website \Magento\Store\Model\Website */
            $ksWebsite = $this->ksStoreManager->getStore($this->getKsProduct()->getStoreId())->getWebsite();

            $this->ksWebsites[$ksWebsite->getId()] = [
                'name' => $ksWebsite->getName(),
                'currency' => $ksWebsite->getBaseCurrencyCode()
            ];
        } elseif (!$this->isScopeGlobal()) {
            $ksWebsites = $this->ksStoreManager->getWebsites();
            $ksProductWebsiteIds = $this->getKsProduct()->getWebsiteIds();
            foreach ($ksWebsites as $ksWebsite) {
                /** @var $website \Magento\Store\Model\Website */
                if (!in_array($ksWebsite->getId(), $ksProductWebsiteIds)) {
                    continue;
                }
                $this->ksWebsites[$ksWebsite->getId()] = [
                    'name' => $ksWebsite->getName(),
                    'currency' => $ksWebsite->getBaseCurrencyCode()
                ];
            }
        }

        return $this->ksWebsites;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @param int|null $ksGroupId  return name by customer group id
     * @return array|string
     */
    public function getKsCustomerGroups($ksGroupId = null)
    {
        if ($this->ksCustomerGroups === null) {
            if (!$this->ksModuleManager->isEnabled('Magento_Customer')) {
                return [];
            }
            $this->ksCustomerGroups = $this->_getKsInitialCustomerGroups();
            /** @var \Magento\Customer\Api\Data\GroupInterface[] $ksGroups */
            $ksGroups = $this->ksGroupRepository->getList($this->ksSearchCriteriaBuilder->create());
            foreach ($ksGroups->getItems() as $ksGroup) {
                $this->ksCustomerGroups[$ksGroup->getId()] = $ksGroup->getCode();
            }
        }

        if ($ksGroupId !== null) {
            return $this->ksCustomerGroups[$ksGroupId] ?? [];
        }

        return $this->ksCustomerGroups;
    }

    /**
     * Retrieve price types
     *
     * @return array|string
     */
    public function getKsPriceTypes()
    {
        return $this->ksTierProductPriceOptions->toOptionArray();
    }

    /**
     * Prepare group price ksValues
     *
     * @return array
     */
    public function getKsTierPriceValues()
    {
        $ksValues = [];
        $ksData = $this->getKsProduct()->getTierPrice();

        if (is_array($ksData)) {
            $ksValues = $ksData;
        }

        foreach ($ksValues as &$ksValue) {
            $ksValue['readonly'] = $ksValue['website_id'] == 0 &&
                $this->isKsShowWebsiteColumn() &&
                !$this->isKsAllowChangeWebsite();
            $ksValue['price'] = $ksValue['price'];
        }

        return $ksValues;
    }

    /**
     * Show group prices grid website column
     *
     * @return bool
     */
    public function isKsShowWebsiteColumn()
    {
        return !$this->ksStoreManager->isSingleStoreMode();
    }

    /**
     * Check is allow change website value for combination
     *
     * @return bool
     */
    public function isKsAllowChangeWebsite()
    {
        if (!$this->isKsShowWebsiteColumn() || $this->getKsProduct()->getStoreId()) {
            return false;
        }
        return true;
    }
}
