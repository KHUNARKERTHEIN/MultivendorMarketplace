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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Items;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo\Item;
use \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory;
use \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory as ShipmentCommentFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory as MemoCommentFactory;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Filter\FilterManager;
use Magento\Downloadable\Model\Link;
use Magento\Store\Model\ScopeInterface;

/**
 * Abstract items renderer
 *
 */
class AbstractItems extends \Magento\Backend\Block\Template
{
    /**
     * Block alias fallbackf
     */
    const DEFAULT_TYPE = 'default';

    /**
     * Renderers for other column with column name key
     * block    => the block name
     * template => the template file
     * renderer => the block object
     *
     * @var array
     */
    protected $_columnRenders = [];

    /**
     * Flag - if it is set method canEditQty will return value of it
     *
     * @var bool|null
     */
    protected $_canEditQty;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $ksSalesData = null;

    /**
    * Sales data
    *
    * @var \Magento\Sales\Helper\Data
    */
    protected $ksOrderHelper;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $ksCommentFactory;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $ksShipmentCommentFactory;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $ksMemoCommentFactory;

    /**
     * @var SalesOrderItem
     */
    protected $ksSalesOrderItem;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $ksPurchasedFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     */
    protected $ksItemsFactory;

    /**
     * @var \Magento\Sales\Model\OrderItem 
     */
    protected $ksSalesItemRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $ksSerializer;
    
    /** 
     * @var FilterManager
    */
    protected $ksFilterManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Data $ksSalesData
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory as ShipmentCommentFactory
     * @param \Magento\Sales\Model\Order\Item $ksSalesOrderItem
     * @param \Magento\Sales\Model\OrderItem $ksSalesItemRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $ksSerializer
     * @param FilterManager $ksFilterManager
     * @param \Magento\Catalog\Helper\Data $ksCatalogHelper\Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory
     * @param array $ksData
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Sales\Helper\Data $ksSalesData,
        \Ksolves\MultivendorMarketplace\Helper\KsOrderHelper $ksOrderHelper,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $ksCommentFactory,
        ShipmentCommentFactory $ksShipmentCommentFactory,
        MemoCommentFactory $ksMemoCommentFactory,
        \Magento\Sales\Model\Order\Item $ksSalesOrderItem,
        \Magento\Sales\Api\OrderItemRepositoryInterface $ksSalesItemRepository,
        Json $ksSerializer,
        FilterManager $ksFilterManager,
        \Magento\Catalog\Helper\Data $ksCatalogHelper,
        \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory,
        array $ksData = []
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        $this->ksRegistry = $ksRegistry;
        $this->priceHelper = $priceHelper;
        $this->ksSalesData = $ksSalesData;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksCommentFactory = $ksCommentFactory;
        $this->ksShipmentCommentFactory = $ksShipmentCommentFactory;
        $this->ksMemoCommentFactory = $ksMemoCommentFactory;
        $this->ksSalesOrderItem =$ksSalesOrderItem;
        $this->ksSalesItemRepository = $ksSalesItemRepository;
        $this->ksSerializer = $ksSerializer;
        $this->ksFilterManager = $ksFilterManager;
        $this->ksPurchasedFactory = $ksPurchasedFactory;
        $this->ksItemsFactory = $ksItemsFactory;
        $ksData['ksCatalogHelper'] = $ksCatalogHelper;
        $ksData['ksOrderHelper'] = $ksOrderHelper;
        parent::__construct($ksContext, $ksData);
    }

    /**
     * Add column renderers
     *
     * @param array $blocks
     * @return $this
     */
    public function setColumnRenders(array $blocks)
    {
        foreach ($blocks as $blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block->getRenderedBlock() === null) {
                $block->setRenderedBlock($this);
            }
            $this->_columnRenders[$blockName] = $block;
        }
        return $this;
    }

    /**
     * Retrieve item renderer block
     *
     * @param string $type
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @throws \RuntimeException
     */
    public function getItemRenderer($type)
    {
        /** @var $renderer \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $renderer = $this->getChildBlock($type) ?: $this->getChildBlock(self::DEFAULT_TYPE);
        if (!$renderer instanceof \Magento\Framework\View\Element\BlockInterface) {
            throw new \RuntimeException('Renderer for type "' . $type . '" does not exist.');
        }
        $renderer->setColumnRenders($this->getLayout()->getGroupChildNames($this->getNameInLayout(), 'column'));

        return $renderer;
    }

    /**
     * Retrieve column renderer block
     *
     * @param string $column
     * @param string $compositePart
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function getColumnRenderer($column, $compositePart = '')
    {
        $column = 'column_' . $column;
        if (isset($this->_columnRenders[$column . '_' . $compositePart])) {
            $column .= '_' . $compositePart;
        }
        if (!isset($this->_columnRenders[$column])) {
            return false;
        }
        return $this->_columnRenders[$column];
    }

    /**
     * @return object
     */
    public function getKsOrderItem($ksOrderItemId)
    {
        return $this->ksSalesItemRepository->get($ksOrderItemId);
    }

    /**
     * Retrieve rendered item html content
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getItemHtml(\Magento\Framework\DataObject $item)
    {
        if ($item->getOrderItem()) {
            $type = $item->getOrderItem()->getProductType();
        } else {
            $type = $item->getProductType();
        }

        return $this->getItemRenderer($type)->setItem($item)->setCanEditQty($this->canEditQty())->toHtml();
    }

    /**
     * Retrieve rendered item extra info html content
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getItemExtraInfoHtml(\Magento\Framework\DataObject $item)
    {
        $extraInfoBlock = $this->getChildBlock('order_item_extra_info');
        if ($extraInfoBlock) {
            return $extraInfoBlock->setItem($item)->toHtml();
        }
        return '';
    }

    /**
     * Get credit memo
     *
     * @return mixed
     */
    public function getCreditmemo()
    {
        return $this->ksRegistry->registry('current_creditmemo');
    }

    /**
     * Retrieve available order
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return Order
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if ($this->ksRegistry->registry('current_order')) {
            return $this->ksRegistry->registry('current_order');
        }
        if ($this->ksRegistry->registry('order')) {
            return $this->ksRegistry->registry('order');
        }
        if ($this->getInvoice()) {
            return $this->getInvoice()->getOrder();
        }
        if ($this->getCreditmemo()) {
            return $this->getCreditmemo()->getOrder();
        }
        if ($this->getItem()->getOrder()) {
            return $this->getItem()->getOrder();
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the order instance right now.'));
    }

    /**
     * Retrieve formatted price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getOrder()->formatPrice($price);
    }

    /**
     * Getting all available children for Invoice, Shipment or CreditMemo item
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     */
    public function getChildren($item)
    {
        $itemsArray = [];

        $items = null;
        if ($item instanceof \Magento\Sales\Model\Order\Invoice\Item) {
            $items = $item->getInvoice()->getAllItems();
        } elseif ($item instanceof \Magento\Sales\Model\Order\Shipment\Item) {
            $items = $item->getShipment()->getAllItems();
        } elseif ($item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) {
            $items = $item->getCreditmemo()->getAllItems();
        }

        if ($items) {
            foreach ($items as $value) {
                $parentItem = $value->getOrderItem()->getParentItem();
                if ($parentItem) {
                    $itemsArray[$parentItem->getId()][$value->getOrderItemId()] = $value;
                } else {
                    $itemsArray[$value->getOrderItem()->getId()][$value->getOrderItemId()] = $value;
                }
            }
        } else {
            $ksParentItem = $this->ksSalesItemRepository->get($item->getKsOrderItemId());
            $ksChildItems = $ksParentItem->getChildrenItems();
            $ksItems = [$ksParentItem];
            foreach ($ksChildItems as $ksItem) {
                array_push($ksItems, $this->ksSalesItemRepository->get($ksItem->getId()));
            }
            return $ksItems;
        }

        if (isset($itemsArray[$item->getOrderItem()->getId()])) {
            return $itemsArray[$item->getOrderItem()->getId()];
        }
        return null;
    }

    /**
     * Retrieve Selection attributes
     *
     * @param \Magento\Framework\DataObject $item
     * @return mixed
     */
    public function getSelectionAttributes($item)
    {
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }
        if (isset($options['bundle_selection_attributes'])) {
            return $this->ksSerializer->unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }

    /**
     * Can show price info for item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function canShowPriceInfo($item)
    {
        if ($item->getOrderItem()) {
            $item = $item->getOrderItem();
        }
        if ($item->getParentItem() && $this->isChildCalculated()
            || !$item->getParentItem() && !$this->isChildCalculated()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve is Child Calculated
     *
     * @param \Magento\Framework\DataObject $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isChildCalculated($item=null)
    {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['product_calculations'])
                        && $options['product_calculations'] == AbstractType::CALCULATE_CHILD);
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    return !(isset($options['product_calculations'])
                        && $options['product_calculations'] == AbstractType::CALCULATE_CHILD);
                }
            }
        }

        $options = $this->getKsBundleOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['product_calculations'])
                && $options['product_calculations'] == AbstractType::CALCULATE_CHILD
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve Value HTML
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getValueHtml($item)
    {
        $result = $this->ksFilterManager->stripTags($item->getName());
        if (!$this->isShipmentSeparately($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                $result = $this->ksFilterManager->sprintf($attributes['qty'], ['format' => '%d']) . ' x ' . $result;
            }
        }
        if (!$this->isChildCalculated($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                $result .= " " . $this->ksFilterManager->stripTags(
                    $this->getKsBundleOrderItem()->getOrder()->formatPrice($attributes['price'])
                );
            }
        }
        return $result;
    }

    /**
     * Retrieve Order options
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getOrderOptions($item)
    {
        $result = [];
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }

    /**
     * Retrieve is Shipment Separately flag for Item
     *
     * @param \Magento\Framework\DataObject $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isShipmentSeparately($item)
    {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['shipment_type'])
                        && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY);
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    return !(isset($options['shipment_type'])
                        && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY);
                }
            }
        }

        $options = $this->getKsBundleOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['shipment_type']) && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY) {
                return true;
            }
        }
        return false;
    }

    /**
     * Truncate string
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string $remainder
     * @param bool $breakWords
     * @return string
     */
    public function truncateString($value, $length = 80, $etc = '...', &$remainder = '', $breakWords = true)
    {
        return $this->ksFilterManager->truncate(
            $value,
            ['length' => $length, 'etc' => $etc, 'remainder' => $remainder, 'breakWords' => $breakWords]
        );
    }

    /**
     * Get Invoiceable Qty
     *
     * @param $ksOrderItemId Integer
     * @return Invoiceable Quantity
     */
    public function getKsInvoiceableQty($ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsInvoiceableQty($ksOrderItemId);
    }

    /**
     * Get Bundled Order Item
     *
     * @return object
     */
    public function getKsBundleOrderItem()
    {
        if ($this->getKsBundleItem()->getOrderItem()) {
            return $this->getKsBundleItem()->getOrderItem();
        } else {
            return $this->getKsOrderItem($this->getKsBundleItem()->getKsOrderItemId());
        }
    }

    /**
     * Return purchased links.
     *
     * @param \Magento\Sales\Model\Order\Item $ksItem
     * @return Object
     */
    public function getKsDownloadableItemData($ksItem)
    {
        $this->ksPurchased = $this->ksPurchasedFactory->create()->load(
            $ksItem->getId(),
            'order_item_id'
        );
        $ksPurchasedItem = $this->ksItemsFactory->create()->addFieldToFilter('order_item_id', $ksItem->getId());
        $this->ksPurchased->setPurchasedItems($ksPurchasedItem);
        return $this->ksPurchased;
    }

    /**
     * Retunrn links title.
     *
     * @param \Magento\Sales\Model\Order\Item $ksItem
     * @return null|string
     */
    public function getKsDownloadableLinkTitle($ksItem)
    {
        return $this->getKsDownloadableItemData($ksItem)->getLinkSectionTitle() ?: $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
