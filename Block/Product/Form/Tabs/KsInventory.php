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
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Source\Backorders;
use Magento\CatalogInventory\Model\Source\Stock;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\JsonValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration\GetData  as KsGetDataModel;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * KsInventory block class
 */
class KsInventory extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{

    /**
     * @var Manager
     */
    protected $ksModuleManager;

    /**
     * @var Stock
     */
    protected $ksStock;

    /**
     * @var Backorders
     */
    protected $ksBackorders;

    /**
     * @var StockRegistryInterface
     */
    protected $ksStockRegistry;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var GetSourceItemsDataBySku
     */
    private $ksGetSourceItemsDataBySku;

    /**
     * @var KsGetDataModel
     */
    private $ksGetDataResourceModel;

    /**
     * @var GroupRepositoryInterface
     */
    private $ksGroupRepository;

    /**
     * @var JsonValidator
     */
    private $ksJsonValidator;

    /**
     * @var ksJson
     */
    private $ksJson;

    /**
     * @var StockConfigurationInterface
     */
    protected $ksStockConfiguration;

    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param Backorders $ksBackorders
     * @param Stock $ksStock
     * @param Manager $ksModuleManager
     * @param StockRegistryInterface $ksStockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param GetSourceItemsDataBySku $ksGetSourceItemsDataBySku
     * @param KsGetDataModel $ksGetDataResourceModel
     * @param GroupRepositoryInterface $ksGroupRepository
     * @param JsonValidator $ksJsonValidator
     * @param Json $ksJson
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        Backorders $ksBackorders,
        Stock $ksStock,
        Manager $ksModuleManager,
        StockRegistryInterface $ksStockRegistry,
        StockConfigurationInterface $stockConfiguration,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        GetSourceItemsDataBySku $ksGetSourceItemsDataBySku,
        GroupRepositoryInterface $ksGroupRepository,
        JsonValidator $ksJsonValidator,
        Json $ksJson,
        KsGetDataModel $ksGetDataResourceModel = null,
        array $ksData = []
    ) {
        $this->ksStock = $ksStock;
        $this->ksBackorders = $ksBackorders;
        $this->ksModuleManager = $ksModuleManager;
        $this->ksStockRegistry = $ksStockRegistry;
        $this->ksStockConfiguration = $stockConfiguration;
        $this->ksGetSourceItemsDataBySku = $ksGetSourceItemsDataBySku;
        $this->ksGetDataResourceModel = $ksGetDataResourceModel ?: ObjectManager::getInstance()->get(KsGetDataModel::class);
        $this->ksGroupRepository = $ksGroupRepository;
        $this->ksJsonValidator = $ksJsonValidator;
        $this->ksJson = $ksJson;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * Check if product type is virtual
     *
     * @return bool
     */
    public function ksGetSourceData()
    {
        $ksSourceArray = [];
        if ($this->getKsProduct()->getId()) {
            $ksSourceCollection = $this->ksGetSourceItemsDataBySku->execute($this->getKsProduct()->getSku());
            foreach ($ksSourceCollection as $key => $ksSource) {
                $ksSourceItemConfigurationData = $this->ksGetDataResourceModel->execute(
                    (string)$ksSource[SourceInterface::SOURCE_CODE],
                    $this->getKsProduct()->getSku()
                );
                $ksSourceItemConfigurationData = $ksSourceItemConfigurationData
                ?: [SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => null];

                $ksNotifyQtyValue = $ksSourceItemConfigurationData[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY];

                $ksNotifyDefaultQty = '0';
                if ($ksNotifyQtyValue === null) {
                    $ksNotifyQtyValue = $this->getDefaultConfigValue('notify_stock_qty');
                    $ksNotifyDefaultQty = '1';
                }

                $ksSourceArray[] = [
                    'index' => $key,
                    'source_code' => $ksSource['source_code'],
                    'name' => $ksSource['name'],
                    'sources_status' => ($ksSource['source_status']==1) ? __('Enabled') : __('Disabled'),
                    'sources_item_status' => $ksSource['status'],
                    'qty' => $ksSource['quantity'],
                    'notify_stock_qty' => $ksNotifyQtyValue,
                    'notify_stock_qty_use_default' => $ksNotifyDefaultQty
                ];
            }
        }
        return $ksSourceArray;
    }

    /**
     * Get backorder option.
     *
     * @return array
     */
    public function getBackordersOption()
    {
        if ($this->ksModuleManager->isEnabled('Magento_CatalogInventory')) {
            return $this->ksBackorders->toOptionArray();
        }

        return [];
    }

    /**
     * Retrieve stock option array
     *
     * @return array
     */
    public function getStockOption()
    {
        if ($this->ksModuleManager->isEnabled('Magento_CatalogInventory')) {
            return $this->ksStock->toOptionArray();
        }

        return [];
    }

    /**
     * Retrieve Catalog Inventory  Stock Item Model
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem()
    {
        return $this->ksStockRegistry->getStockItem(
            $this->getKsProduct()->getId(),
            $this->getKsProduct()->getStore()->getWebsiteId()
        );
    }

    /**
     * Get field value.
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldValue($ksField)
    {
        $ksStockItem = $this->getStockItem();
        $ksValue = null;
        if ($ksStockItem->getItemId()) {
            $ksMethod = 'get' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($ksField);
            if (is_callable([$ksStockItem, $ksMethod])) {
                $ksValue = $ksStockItem->{$ksMethod}();
            }
        }
        return $ksValue === null ? $this->ksStockConfiguration->getDefaultConfigValue($ksField) : $ksValue;
    }

    /**
     * Get config field value.
     *
     * @param string $ksField
     * @return string|null
     */
    public function getConfigFieldValue($ksField)
    {
        $ksStockItem = $this->getStockItem();
        if ($ksStockItem->getItemId()) {
            $ksMethod = 'getUseConfig' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase(
                $ksField
            );
            if (method_exists($ksStockItem, $ksMethod)) {
                return $ksStockItem->{$ksMethod}();
            }
        }
        return $this->ksStockConfiguration->getDefaultConfigValue($ksField);
    }

    /**
     * Get default config value.
     *
     * @param string $ksField
     * @return string|null
     */
    public function getDefaultConfigValue($ksField)
    {
        return $this->ksStockConfiguration->getDefaultConfigValue($ksField);
    }

    /**
     * Is readonly stock
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->getKsProduct()->getInventoryReadonly();
    }

    /**
     * Is new.
     *
     * @return bool
     */
    public function isNew()
    {
        if ($this->getKsProduct()->getId()) {
            return false;
        }
        return true;
    }

    /**
     * Check Whether product type can have fractional quantity or not
     *
     * @return bool
     */
    public function canUseQtyDecimals()
    {
        return $this->getKsProduct()->getTypeInstance()->canUseQtyDecimals();
    }

    /**
     * Check if product type is virtual
     *
     * @return bool
     */
    public function isVirtual()
    {
        return $this->getKsProduct()->getIsVirtual();
    }

    /**
     * Check if product type is virtual
     *
     * @return bool
     */
    public function getKsDefaultMinQty()
    {
        $ksMinSaleQtyData = $this->ksStockConfiguration->getDefaultConfigValue(StockItemInterface::MIN_SALE_QTY);

        if (is_string($ksMinSaleQtyData) && $this->ksJsonValidator->isValid($ksMinSaleQtyData)) {
            // Set data source for dynamicRows minimum qty allowed in shopping cart
            $ksUnserializedMinSaleQty = $this->ksJson->unserialize($ksMinSaleQtyData);

            if (is_array($ksUnserializedMinSaleQty)) {
                $ksMinSaleQtyData = array_map(
                    function ($group, $qty) {
                        return [
                            StockItemInterface::CUSTOMER_GROUP_ID => $group,
                            StockItemInterface::MIN_SALE_QTY => $qty
                        ];
                    },
                    array_keys($ksUnserializedMinSaleQty),
                    array_values($ksUnserializedMinSaleQty)
                );
            }
        }

        return $ksMinSaleQtyData;
    }

    /**
     * Retrieve Catalog Inventory Min Qty
     *
     * @return int
     */
    public function getKsProductMinQty()
    {
        if ($this->getKsProduct()->getId()) {
            return $this->getStockItem()->getMinSaleQty();
        } else {
            return 1;
        }
    }

    /**
     * get Customer Group code by Id
     * @param $ksCustomerGroupId
     * @return string
     */
    public function getKsCustomerGroupCode($ksCustomerGroupId)
    {
        if ($ksCustomerGroupId==32000) {
            $ksCustomerGroup = __('ALL GROUPS');
        } else {
            $ksGroup = $this->ksGroupRepository->getById($ksCustomerGroupId);
            $ksCustomerGroup = $ksGroup->getCode();
        }
        return $ksCustomerGroup;
    }
}
