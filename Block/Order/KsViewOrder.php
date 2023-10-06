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

use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Sales\Model\Order\Item;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Sales\Helper\Admin;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem;

/**
 * KsViewOrder block
 */
class KsViewOrder extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    
    /**
     * @var AddressRenderer
     */
    protected $addressRenderer;
    
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeConfig;
    
    /**
     * @var StoreManager
     */
    protected $ksStoreManager;

    /**
     * @var SellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesOrder
     */
    protected $ksSalesOrder;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
    * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
    */
    protected $ksLocaleDate;

    /**
     * Customer service
     *
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $ksMetadata;

    /**
     * Metadata element factory
     *
     * @var \Magento\Customer\Model\Metadata\ElementFactory
     */
    protected $ksMetadataElementFactory;

    /**
     * Admin helper
     *
     * @var \Magento\Sales\Helper\Admin
     */
    protected $ksAdminHelper;

    /**
     * @var KsSalesOrderItem
     */
    protected $ksSalesOrderItem;

    /**
     * @var Item
     */
    protected $salesOrderItem;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param AddressRenderer $addressRenderer
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper;
     * @param \Ksolves\Multivendor\Model\OrderFactory $ksOrderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Api\CustomerMetadataInterface
     * @param \Magento\Customer\Model\Metadata\ElementFactory
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param KsDataHelper $ksDataHelper
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        AddressRenderer $addressRenderer,
        ScopeConfigInterface $ksScopeConfig,
        KsSellerHelper $ksSellerHelper,
        KsSalesOrder $ksSalesOrder,
        StoreManagerInterface  $ksStoreManager,
        CollectionFactory $orderCollectionFactory,
        TimezoneInterface $ksLocaleDate,
        CustomerMetadataInterface $ksMetadata,
        ElementFactory $ksMetadataElementFactory,
        Admin $ksAdminHelper,
        Item $salesOrderItem,
        KsSalesOrderItem $ksSalesOrderItem,
        KsDataHelper $ksDataHelper
    ) {
        $this->coreRegistry = $registry;
        $this->addressRenderer = $addressRenderer;
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSalesOrder = $ksSalesOrder;
        $this->orderCollectionFactory =$orderCollectionFactory;
        $this->ksLocaleDate =$ksLocaleDate;
        $this->ksMetadata =$ksMetadata;
        $this->ksMetadataElementFactory =$ksMetadataElementFactory;
        $this->ksAdminHelper =$ksAdminHelper;
        $this->ksSalesOrderItem=$ksSalesOrderItem;
        $this->salesOrderItem = $salesOrderItem;
        $this->ksDataHelper=$ksDataHelper;
        parent::__construct($context);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getKsOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
    
    /**
     * Returns string with formatted address
     *
     * @param Address $address
     * @return null|string
     */
    public function getFormattedAddress($address)
    {
        return $this->addressRenderer->format($address, 'html');
    }
    
    /**
     * @return string
     */
    public function checkShipmentAllowed()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_shipment_settings/ks_create_shipment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return string
     */
    public function checkCancelAllowed()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_order_settings/ks_cancel_the_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return string
     */
    public function checkCreditMemoAllowed()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_creditmemo_settings/ks_create_creditmemo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return string
     */
    public function checkHoldAllowed()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_order_settings/ks_hold_the_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return string
     */
    public function checkInvoiceAllowed()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_invoice_settings/ks_create_invoice',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return string
     */
    public function checkSendEmailAllowed()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_order_settings/ks_send_order_mail',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * get seller cancelled item
     * @return int
     */
    public function getKsSellerCancelItem()
    {
        $vendorCancelItem = 0;
        $order = $this->getKsOrder();
        foreach ($order->getAllItems() as $orderItem) {
            if ($this->ksSellerHelper->KsIsSellerOrderItem($orderItem->getProductId(), $order->getId())) {
                if ($orderItem->getQtyCanceled()!=0) {
                    $vendorCancelItem = 1;
                }
            }
        }
        return $vendorCancelItem;
    }

    /**
     * Get order store name
     *
     * @return null|string
     */
    public function getOrderStoreName()
    {
        if ($this->getKsOrder()) {
            $storeId = $this->getKsOrder()->getStoreId();
            if ($storeId === null) {
                $deleted = __(' [deleted]');
                return nl2br($this->getKsOrder()->getStoreName()) . $deleted;
            }
            $store = $this->ksStoreManager->getStore($storeId);
            $name = [$store->getWebsite()->getName(), $store->getGroup()->getName(), $store->getName()];
            return implode('<br/>', $name);
        }

        return null;
    }

    /**
     * Get object created at date
     *
     * @param string $createdAt
     * @return \DateTime
     */
    public function getKsOrderAdminDate($createdAt)
    {
        return $this->ksLocaleDate->date(new \DateTime($createdAt));
    }

    /**
     * Check if is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->ksStoreManager->isSingleStoreMode();
    }

    /**
     * Get URL to edit the customer.
     *
     * @return string
     */
    public function getKsCustomerViewUrl()
    {
        if ($this->getKsOrder()->getCustomerIsGuest() || !$this->getKsOrder()->getCustomerId()) {
            return '';
        }

        return $this->getUrl('customer/index/edit', ['id' => $this->getKsOrder()->getCustomerId()]);
    }

    /**
    * Return array of additional account data
    * Value is option style array
    *
    * @return array
    */
    public function getKsCustomerAccountData()
    {
        $accountData = [];
        $entityType = 'customer';

        /* @var \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute */
        foreach ($this->ksMetadata->getAllAttributesMetadata($entityType) as $attribute) {
            if (!$attribute->isVisible() || $attribute->isSystem()) {
                continue;
            }
            $orderKey = sprintf('customer_%s', $attribute->getAttributeCode());
            $orderValue = $this->getKsOrder()->getData($orderKey);
            if ($orderValue != '') {
                $metadataElement = $this->ksMetadataElementFactory->create($attribute, $orderValue, $entityType);
                $value = $metadataElement->outputValue(AttributeDataFactory::OUTPUT_FORMAT_HTML);
                $sortOrder = $attribute->getSortOrder() + $attribute->isUserDefined() ? 200 : 0;
                $sortOrder = $this->ksPrepareAccountDataSortOrder($accountData, $sortOrder);
                $accountData[$sortOrder] = [
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $this->escapeHtml($value, ['br']),
                ];
            }
        }
        ksort($accountData, SORT_NUMERIC);

        return $accountData;
    }

    /**
     * Find sort order for account data
     * Sort Order used as array key
     *
     * @param array $data
     * @param int $sortOrder
     * @return int
     */
    protected function ksPrepareAccountDataSortOrder(array $data, $sortOrder)
    {
        if (isset($data[$sortOrder])) {
            return $this->ksPrepareAccountDataSortOrder($data, $sortOrder + 1);
        }

        return $sortOrder;
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function getChildHtml($alias = '', $useCache = true)
    {
        $layout = $this->getLayout();

        if ($alias || !$layout) {
            return parent::getChildHtml($alias, $useCache);
        }

        $childNames = $layout->getChildNames($this->getNameInLayout());
        $outputChildNames = array_diff($childNames, ['extra_customer_info']);

        $out = '';
        foreach ($outputChildNames as $childName) {
            $out .= $layout->renderElement($childName, $useCache);
        }

        return $out;
    }

    /**
    * Get payment html
    *
    * @return string
    */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }

    /**
     * Retrieve gift options container block html
     *
     * @return string
     */
    public function getGiftOptionsHtml()
    {
        return $this->getChildHtml('gift_options');
    }

    /**
     * Indicates that block can display giftmessages form
     *
     * @return bool
     */
    public function canDisplayGiftmessage()
    {
        return $this->_messageHelper->isMessagesAllowed(
            'order',
            $this->getEntity(),
            $this->getEntity()->getStoreId()
        );
    }

    /**
     * Get items html
     *
     * @return string
     */
    public function getItemsHtml()
    {
        return $this->getChildHtml('order_items');
    }

    /**
     * Display price attribute
     *
     * @param string $code
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayPriceAttribute($code, $strong = false, $separator = '<br/>')
    {
        return $this->ksAdminHelper->displayPriceAttribute($this->getPriceDataObject(), $code, $strong, $separator);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getInvoiceUrl($order)
    {
        return $this->getUrl('multivendor/order_invoice/new', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getShipmentUrl($order)
    {
        return $this->getUrl('multivendor/order_shipment/new', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getCreditmemoUrl($order)
    {
        return $this->getUrl('multivendor/order_creditmemo/new', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getKsSendEmailUrl($order)
    {
        return $this->getUrl('multivendor/order/sendemail', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getKsSendEmailAllowed()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_order_settings/ks_send_order_mail',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * Get Back Url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('multivendor/order/listing');
    }

    /**
     * Get Sales Order Collection
     *
     * @return array|null
     */
    public function getKsSalesOrder()
    {
        $ksSalesOrderCollection= $this->ksSalesOrder->getCollection()->addFieldToFilter('ks_order_id', $this->getKsOrder()->getId())->addFieldToFilter('ks_seller_id', $this->ksDataHelper->getKsCustomerId())->getFirstItem();
        return $ksSalesOrderCollection;
    }

    /**
     * Retrieve order shipment availability
     *
     * @return bool
     */
    public function ksCanShip()
    {
        return $this->getKsSalesOrder()->ksCanShip();
    }

    /**
     * Retrieve order invoice availability
     *
     * @return bool
     */
    public function ksCanInvoice()
    {
        return $this->getKsSalesOrder()->ksCanInvoice();
    }

    /**
     * Retrieve order credit memo (refund) availability
     *
     * @return bool
     */
    public function ksCanCreditmemo()
    {
        return $this->getKsSalesOrder()->ksCanCreditmemo();
    }
    
    /**
     * Return Is Virtual
     *
     * @return int
     */
    public function ksIsVirtual()
    {
        return $this->getKsSalesOrder()->ksIsVirtual();
    }

    /**
     * Get order view URL.
     *
     * @param int $orderId
     * @return string
     */
    public function getViewUrl($orderId)
    {
        return $this->getUrl('multivendor/order/vieworder', ['ks_order_id' => $orderId]);
    }
}
