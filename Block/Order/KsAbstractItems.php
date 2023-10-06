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

namespace Ksolves\MultivendorMarketplace\Block\Order;

use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory;
use Magento\Catalog\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Filter\FilterManager;
use Magento\Downloadable\Model\Link;
use Magento\Store\Model\ScopeInterface;

/**
 * Invoice items grid
 */
class KsAbstractItems extends \Magento\Framework\View\Element\Template
{
    /**
     * Disable submit button
     *
     * @var bool
     */
    protected $_disableSubmitButton = false;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Sales\Model\OrderItem
     */
    protected $ksSalesItemModel;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $ksAdminHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $ksSerializer;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $ksPurchasedFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     */
    protected $ksItemsFactory;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    protected $ksSalesItemRepository;

    /**
     * @var FilterManager
     */
    protected $ksFilterManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Model\OrderItem $ksSalesItemRepository
     * @param KsOrderHeler $ksOrderHelper
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $ksSerializer
     * @param FilterManager $ksFilterManager
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory
     * @param array $ksData
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Api\OrderItemRepositoryInterface $ksSalesItemRepository,
        KsOrderHelper $ksOrderHelper,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        \Magento\Catalog\Helper\Data $ksCatalogHelper,
        Json $ksSerializer,
        FilterManager $ksFilterManager,
        \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory,
        array $ksData = []
    ) {
        $this->ksSalesItemRepository = $ksSalesItemRepository;
        $this->ksRegistry = $ksRegistry;
        $this->ksOrderHelper  = $ksOrderHelper;
        $this->ksAdminHelper = $ksAdminHelper;
        $this->ksSerializer = $ksSerializer;
        $this->ksFilterManager = $ksFilterManager;
        $this->ksPurchasedFactory = $ksPurchasedFactory;
        $this->ksItemsFactory = $ksItemsFactory;
        $ksData['ksOrderHelper'] = $ksOrderHelper;
        $ksData['ksCatalogHelper'] = $ksCatalogHelper;
        parent::__construct($ksContext, $ksData);
    }

    /**
     * @return object
     */
    public function getKsOrder()
    {
        return $this->ksRegistry->registry('current_order');
    }

    /**
     * @return object
     */
    public function getKsOrderItem($ksOrderItemId)
    {
        return $this->ksSalesItemRepository->get($ksOrderItemId);
    }

    /**
     * Replace links in string
     *
     * @param array|string $data
     * @param null|array $allowedTags
     * @return string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->ksAdminHelper->escapeHtmlWithLinks($data, $allowedTags);
    }

    /**
     * @param integer $ksIncrementId
     * @return integer
     */
    public function getKsSellerTotalOrderCommission($ksIncrementId)
    {
        $this->ksOrderHelper->getKsSellerTotalOrderCommission($ksIncrementId);
    }

    /**
     * @param integer $ksOrderId
     * @return integer
     */
    public function getKsOrderIncrementId($ksOrderId)
    {
        return $this->ksOrderHelper->getKsOrderIncrementId($ksOrderId);
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
        if ($item->getOrderItem()) {
            $item=$item->getOrderItem();
        }
        if (!$this->isChildCalculated($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                $result .= " " . $this->ksFilterManager->stripTags(
                    $item->getOrder()->formatPrice($attributes['price'])
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
