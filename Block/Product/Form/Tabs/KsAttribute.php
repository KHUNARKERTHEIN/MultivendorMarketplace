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
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration\GetData as KsGetDataModel;
use Magento\Framework\App\ObjectManager;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Boolean;

/**
 * KsAttribute block class
 */
class KsAttribute extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{
    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var CurrencyFactory
     */
    protected $ksCurrencyFactory;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * Tabs structure
     *
     * @var array
     */
    protected $_ksFields;

    /**
     * GetSalableQuantityDataBySku
     */
    protected $ksGetScalableQuantity;

    /**
     * ScopeOverriddenValue
     */
    protected $ksScopeOverriddenValue;

    /**
     * @var GetSourceItemsDataBySku
     */
    protected $ksGetSourceItemsDataBySku;

    /**
     * @var KsGetDataModel
     */
    private $ksGetDataResourceModel;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param StoreManagerInterface $ksStoreManager
     * @param CurrencyFactory $ksCurrencyFactory
     * @param Registry $ksRegistry
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param GetSalableQuantityDataBySku $ksGetScalableQuantity
     * @param ScopeOverriddenValue $ksScopeOverriddenValue
     * @param GetSourceItemsDataBySku $ksGetSourceItemsDataBySku
     * @param KsGetDataModel $ksGetDataResourceModel
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        StoreManagerInterface $ksStoreManager,
        CurrencyFactory $ksCurrencyFactory,
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
        $this->ksStoreManager            = $ksStoreManager;
        $this->ksCurrencyFactory         = $ksCurrencyFactory;
        $this->ksDataHelper              = $ksDataHelper;
        $this->ksGetScalableQuantity     = $ksGetScalableQuantity;
        $this->ksScopeOverriddenValue    = $ksScopeOverriddenValue;
        $this->ksGetSourceItemsDataBySku = $ksGetSourceItemsDataBySku;
        $this->ksGetDataResourceModel    = $ksGetDataResourceModel ?: ObjectManager::getInstance()->get(KsGetDataModel::class);
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * Retrieve scope label
     *
     * @param ProductAttributeInterface $ksAttribute
     * @return \Magento\Framework\Phrase|string
     */
    public function getKsScopeLabel(ProductAttributeInterface $ksAttribute)
    {
        if ($this->ksStoreManager->isSingleStoreMode()
            || $ksAttribute->getFrontendInput() === AttributeInterface::FRONTEND_INPUT
        ) {
            return '';
        }

        switch ($ksAttribute->getScope()) {
            case ProductAttributeInterface::SCOPE_GLOBAL_TEXT:
                return __('[GLOBAL]');
            case ProductAttributeInterface::SCOPE_WEBSITE_TEXT:
                return __('[WEBSITE]');
            case ProductAttributeInterface::SCOPE_STORE_TEXT:
                return __('[STORE VIEW]');
        }

        return '';
    }

    /**
     * @return bool
     */
    public function getKsProductId()
    {
        return $this->ksRegistry->registry('product')->getId() ? true : false;
    }

    /**
     * Retrieve field array
     *
     * @return array
     */
    public function getKsFields()
    {
        $ksAttributes = $this->getGroupAttributes();
        $this->_ksFields = [];
        $i = 1;

        foreach ($ksAttributes as $ksAttribute) {
            $i++;
            $ksSortOrder = 10 * $i;
            $ksAttributeCode = $ksAttribute->getAttributeCode();
            $this->addKsField($ksAttributeCode, $ksAttribute, $ksSortOrder);
        }

        //set id for product edit
        if ($this->getGroup()->getAttributeGroupCode() == 'product-details') {
            if ($this->getRequest()->getParam('id')) {
                $ksFieldId = 'id';
                $ksField = $this->ksHiddenField($ksFieldId, $this->getRequest()->getParam('id'));
                $this->addKsField($ksFieldId, $ksField, '1');
            }

            if ($this->getRequest()->getParam('parent_id')) {
                $ksFieldId = 'parent_id';
                $ksField = $this->ksHiddenField($ksFieldId, $this->getRequest()->getParam('parent_id'));
                $this->addKsField($ksFieldId, $ksField, '1');
            }

            //set attr set for product edit
            if ($this->getRequest()->getParam('set')) {
                $ksFieldId = 'set';
                $ksField = $this->ksHiddenField($ksFieldId, $this->getRequest()->getParam('set'));
                $this->addKsField($ksFieldId, $ksField, '2');
            }

            //set type for product edit
            if ($this->getRequest()->getParam('type')) {
                $ksFieldId = 'type';
                $ksField = $this->ksHiddenField($ksFieldId, $this->getRequest()->getParam('type'));
                $this->addKsField($ksFieldId, $ksField, '3');
            }

            //set store for product edit
            if ($this->getRequest()->getParam('store')) {
                $ksFieldId = 'store';
                $ksField = $this->ksHiddenField($ksFieldId, $this->getRequest()->getParam('store'));
                $this->addKsField($ksFieldId, $ksField, '4');
            }

            $ksField = $this->ksHiddenField("ks_product_type", $this->getKsProduct()->getTypeId());
            $this->addKsField("ks_product_type", $ksField, '5');
        }

        return $this->_ksFields;
    }

    /**
     * Whether attribute can have default value
     *
     * @param ProductAttributeInterface $ksAttribute
     * @return bool
     */
    public function ksCanDisplayUseDefault(ProductAttributeInterface $ksAttribute)
    {
        /** @var Product $product */
        $ksProduct = $this->getKsProduct();

        if (!$ksAttribute->isScopeGlobal() &&
            $ksProduct &&
            $ksProduct->getId() &&
            $ksProduct->getStoreId()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check default value usage fact
     *
     * @param ProductAttributeInterface $ksAttribute
     * @return bool
     */
    public function ksUsedDefault(ProductAttributeInterface $ksAttribute)
    {
        return !$this->ksScopeOverriddenValue->containsValue(
            \Magento\Catalog\Api\Data\ProductInterface::class,
            $this->getKsProduct(),
            $ksAttribute->getAttributeCode(),
            $this->getRequest()->getParam('store', 0)
        );
    }

    /**
     * Retrieve field html
     *
     * @return DataObject
     */
    public function getKsFieldHtml($ksField)
    {
        if ($ksField instanceof ProductAttributeInterface) {
            switch ($ksField->getAttributeCode()) {
                case 'quantity_and_stock_status':
                    $ksFieldHtml = $this->getLayout()
                        ->createBlock('Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\KsInventory')
                        ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/ks-inventory.phtml');
                    return $ksFieldHtml->setKsField($ksField)->toHtml();

                case 'swatch_image':
                    break;

                case 'media_image':
                    break;

                default:
                    $ksFieldHtml = $this->getLayout()
                        ->createBlock('Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\KsAttribute')
                        ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/ks-field.phtml');
                    return $ksFieldHtml->setKsField($ksField)->toHtml();
            }
        } elseif ($ksField->getType() == 'hidden') {
            $ksFieldHtml = $this->getLayout()
                ->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/renderer/ks-element.phtml');

            return $ksFieldHtml->setKsElement($ksField)->toHtml();
        }
    }

    /**
     * add hidden field
     *
     * @return array
     */
    public function addKsField($ksFieldId, $ksField, $ksSortOrder)
    {
        $this->_ksFields[$ksFieldId] = $ksField;
    }

    /**
     * Retrieve field array
     *
     * @return array
     */
    private function ksHiddenField($ksFieldId, $ksValue)
    {
        $ksField = new \Magento\Framework\DataObject();
        $ksField->setData(
            [
                'type' => 'hidden',
                'name' => $ksFieldId,
                'value' => $ksValue
            ]
        );
        return $ksField;
    }

    /**
     * Retrieve element html
     *
     * @param ProductAttributeInterface $ksAttribute
     * @return DataObject
     */
    public function getKsAttributeElementHtml(ProductAttributeInterface $ksAttribute)
    {
        if ($ksAttribute->getAttributeCode() == 'category_ids') {
            $ksAttributeHtml = $this->getLayout()
                ->createBlock('Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\KsCategory')
                ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/ks-category.phtml');
            return $ksAttributeHtml->toHtml();
        } else {
            $ksAttributeHtml = $this->getLayout()
                ->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/renderer/ks-element.phtml');

            $ksAttributeCode = $ksAttribute->getAttributeCode();
            $ksBundleArray = ["sku_type","price_type","weight_type"];

            $ksOptions = $ksAttribute->usesSource() ? $ksAttribute->getSource()->getAllOptions() : [];
            $ksClass = $ksAttribute->getIsRequired() && !in_array($ksAttributeCode, $ksBundleArray) ? ' required-entry' : '';
            $ksClass .= $ksAttribute->getFrontendInput() === 'price' ? ' validate-zero-or-greater validate-number validate-not-negative-number' : '';
            $ksClass .= $ksAttribute->getFrontendInput() === 'weight' ? ' validate-zero-or-greater' : '';
            $ksClass .= $ksAttributeCode == 'sku' ? ' no-marginal-whitespace validate-length maximum-length-64' : '';

            $ksClass .= $ksAttribute->getIsWysiwygEnabled() ? 'ks-wysiwyg' : '';

            $ksProduct = $this->getKsProduct();
            $ksValue = '';
            if ($ksAttributeCode != 'tier_price') {
                $ksValue = $ksProduct->getData($ksAttribute->getAttributeCode());
                if ($ksAttribute->getFrontendInput() === 'date' && $ksValue) {
                    $ksDate = date_create($ksValue);
                    $ksValue = date_format($ksDate, "m/d/Y");
                }
                if (!$ksValue && $ksAttribute->getFrontendInput() !== 'date') {
                    $ksValue = $ksAttribute->getDefaultValue();
                }
                if ($ksAttributeCode == 'gift_message_available' && ($ksValue == Boolean::VALUE_USE_CONFIG || !$this->getKsProduct()->getId())) {
                    if ($this->getKsProduct()->getId()) {
                        $ksStoreId = $this->getRequest()->getParam('store', 0);
                    } else {
                        $ksStoreId = $this->ksStoreManager->getDefaultStoreView()->getStoreId();
                    }
                    $ksValue = $this->ksDataHelper->getKsConfigValue('sales/gift_options/allow_items', $ksStoreId);
                }
            }


            if (!$this->getKsProduct()->getId()) {
                $ksPersistorProductData = $this->getKsPersistorProductData();
                if (!empty($ksPersistorProductData) && array_key_exists("product", $ksPersistorProductData)) {
                    if (array_key_exists($ksAttributeCode, $ksPersistorProductData['product'])) {
                        if (is_array($ksPersistorProductData['product'][$ksAttributeCode])) {
                            $ksValue = implode(",", $ksPersistorProductData['product'][$ksAttributeCode]);
                        } else {
                            $ksValue = $ksPersistorProductData['product'][$ksAttributeCode];
                        }
                    }
                }
            }

            $ksElement = new \Magento\Framework\DataObject();
            $ksElement->setData(
                [
                    'type' => $this->getKsElementType($ksAttribute),
                    'name' => 'product[' . $ksAttributeCode . ']',
                    'id' => 'product_' . $ksAttributeCode,
                    'title' => $ksAttribute->getDefaultFrontendLabel(),
                    'options' => $ksOptions,
                    'class' => $ksClass,
                    'value' => $ksValue,
                    'attribute_code' => $ksAttributeCode
                ]
            );

            $ksAttributeHtml->setKsElement($ksElement)
                ->setKsProduct($ksProduct)
                ->setPriceSymbol($this->getKsCurrentCurrency())
                ->setKsWeightUnit($this->getKsWeightUnit());

            return $ksAttributeHtml->toHtml();
        }
    }

    /**
     * Retrieve value element type
     * @param ProductAttributeInterface $ksAttribute
     * @return string
     */
    public function getKsElementType(ProductAttributeInterface $ksAttribute)
    {
        switch ($ksAttribute->getFrontendInput()) {
            case 'select':
                if ($ksAttribute->getAttributeCode() == 'status' || $ksAttribute->getAttributeCode() == 'gift_message_available') {
                    return 'toggle';
                }
                return 'select';
            case 'multiselect':
                return 'multiselect';

            case 'textarea':
                if ($ksAttribute->getIsWysiwygEnabled()) {
                    return 'wysiwyg';
                }
                return 'textarea';

            case 'multiselect':
                return 'multiselect';

            case 'price':
                return 'price';

            case 'weight':
                return 'weight';

            case 'date':
                return 'date';
            case 'boolean':
                return 'toggle';
            case 'datetime':
                return 'datetime';
            case 'weee':
                return 'weee';
            default:
                return 'text';
        }
    }

    /**
     * Retrieve currency symbol
     * @return string
     */
    public function getKsCurrentCurrency()
    {
        $ksStoreId = $this->getRequest()->getParam('store', 0);
        $ksCurrencyCode = $this->ksStoreManager->getStore($ksStoreId)->getBaseCurrencyCode();
        $ksCurrency = $this->ksCurrencyFactory->create()->load($ksCurrencyCode);
        return $ksCurrency->getCurrencySymbol();
    }

    /**
     * Retrieve weight symbol
     * @return string
     */
    public function getKsWeightUnit()
    {
        $ksStoreId = $this->getKsProduct()->getStoreId();
        return $this->ksDataHelper->getKsConfigValue('general/locale/weight_unit', $ksStoreId);
    }

    /**
     * @return array
     */
    public function getKsScalableQuantity()
    {
        if ($this->getRequest()->getParam('id') && $this->getKsProduct()->getTypeId()!='configurable') {
            $ksProduct = $this->getKsProduct()->getSku();
            $ksSalable = $this->ksGetScalableQuantity->execute($ksProduct);
            return $ksSalable;
        }
        return '';
    }
}
