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

namespace Ksolves\MultivendorMarketplace\Helper;

use Magento\Framework\App\Helper\Context;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentItem;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemoItem;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoice;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceItem;
use Magento\Backend\Model\Session;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product\Type\AbstractType;
use \Magento\Weee\Helper\Data as KsWeeeHelperData;

/**
 * KsOrderHelper class
 */
class KsOrderHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_PAYMENT_METHODS = 'payment';

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsCommissionRule
     */
    protected $ksCommissionRule;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesOrder
     */
    protected $ksSalesOrder;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem
     */
    protected $ksSalesOrderItem;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsCommissionHelper
     */
    protected $ksCommissionHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * @var KsSalesShipmentItem
     */
    protected $ksSalesShipmentItem;

    /**
     * @var KsSalesShipmentTrack
     */
    protected $ksSalesShipmentTrack;

    /**
     * @var Shipment
     */
    protected $ksShipmentModel;

    /**
     * @var KsSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * @var KsSalesInvoiceItem
     */
    protected $ksSalesInvoiceItem;

    /**
     * @var Shipment
     */
    protected $ksInvoiceModel;

    protected $ksCurrentCommissionId;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProduct;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $ksPriceHelper;

    /**
     * @var KsSalesCreditMemo
     */
    protected $ksSalesCreditMemo;

    /**
     * @var KsSalesCreditMemoItem
     */
    protected $ksSalesCreditMemoItem;

    /**
     * @var  \Magento\Sales\Model\Order\CreditMemo
     */
    protected $ksCreditMemoModel;

    /**
     * @var  \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $ksConfigurbaleProductModel;

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
     * @var Session
     */
    protected $ksSession;

    /**
     * @var Renderer
     */
    protected $ksAddressRenderer;

    /**
     * @var CurrencyFactory
     */
    protected $ksCurrencyFactory;

    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @var KsWeeeHelperData
     */
    protected $ksWeeeHelper;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    protected $ksSalesItemModel;

    /**
     * @var Renderer
     */
    protected $ksAddksAddressRenderer;

    /**
     * @var Order
     */
    protected $salesOrder;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksCommissionRule
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesOrder $ksSalesOrder
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem $ksSalesOrderItem
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsCommissionHelper $ksCommissionHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProduct
     * @param \Magento\Framework\Pricing\Helper\Data $ksPriceHelper
     * @param KsSalesShipment $ksSalesShipment
     * @param KsSalesShipmentItem $ksSalesShipmentItem
     * @param KsSalesShipmentTrack $ksSalesShipmentTrack
     * @param KsSalesCreditMemo $ksSalesCreditMemo
     * @param KsSalesCreditMemoItem $ksSalesCreditMemoItem
     * @param \Magento\Sales\Model\Order\Shipment $ksShipmentModel
     * @param \Magento\Sales\Model\Order\Creditmemo $ksCreditMemoModel
     * @param KsSalesInvoice $ksSalesInvoice
     * @param KsSalesInvoiceItem $ksSalesInvoiceItem
     * @param \Magento\Sales\Model\Order\Invoice $ksInvoiceModel
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $ksConfigurbaleProductModel
     * @param \Magento\Store\Model\StoreManagerInterface  $ksStoreManager
     * @param Session $ksSession
     * @param Renderer $ksAddressRenderer
     * @param Registry $ksRegistry
     * @param KsWeeeHelperData $ksWeeeHelper
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksCommissionRule,
        \Ksolves\MultivendorMarketplace\Model\KsSalesOrder $ksSalesOrder,
        \Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem $ksSalesOrderItem,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsCommissionHelper $ksCommissionHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProduct,
        \Magento\Framework\Pricing\Helper\Data $ksPriceHelper,
        KsSalesShipment $ksSalesShipment,
        KsSalesShipmentItem $ksSalesShipmentItem,
        KsSalesShipmentTrack $ksSalesShipmentTrack,
        KsSalesCreditMemo $ksSalesCreditMemo,
        KsSalesCreditMemoItem $ksSalesCreditMemoItem,
        \Magento\Sales\Model\Order\Shipment $ksShipmentModel,
        \Magento\Sales\Model\Order\Creditmemo $ksCreditMemoModel,
        KsSalesInvoice $ksSalesInvoice,
        KsSalesInvoiceItem $ksSalesInvoiceItem,
        \Magento\Sales\Model\Order\Invoice $ksInvoiceModel,
        \Magento\Sales\Api\OrderItemRepositoryInterface $ksSalesItemModel,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $ksConfigurbaleProductModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Store\Model\StoreManagerInterface  $ksStoreManager,
        Session $ksSession,
        Renderer $ksAddressRenderer,
        CurrencyFactory $ksCurrencyFactory,
        Registry $ksRegistry,
        Order $salesOrder,
        KsWeeeHelperData $ksWeeeHelper
    ) {
        $this->ksProductFactory           = $ksProductFactory;
        $this->ksCommissionRule           = $ksCommissionRule;
        $this->ksSalesOrder               = $ksSalesOrder;
        $this->ksSalesOrderItem           = $ksSalesOrderItem;
        $this->ksDataHelper               = $ksDataHelper;
        $this->ksEmailHelper              = $ksEmailHelper;
        $this->ksDate                     = $ksDate;
        $this->ksCommissionHelper         = $ksCommissionHelper;
        $this->ksProductHelper            = $ksProductHelper;
        $this->ksProduct                  = $ksProduct;
        $this->ksSellerHelper             = $ksSellerHelper;
        $this->ksPriceHelper              = $ksPriceHelper;
        $this->ksSalesShipment            = $ksSalesShipment;
        $this->ksSalesShipmentItem        = $ksSalesShipmentItem;
        $this->ksSalesShipmentTrack       = $ksSalesShipmentTrack;
        $this->ksShipmentModel            = $ksShipmentModel;
        $this->ksSalesCreditMemo          = $ksSalesCreditMemo;
        $this->ksSalesCreditMemoItem      = $ksSalesCreditMemoItem;
        $this->ksCreditMemoModel          = $ksCreditMemoModel;
        $this->ksSalesInvoice             = $ksSalesInvoice;
        $this->ksSalesInvoiceItem         = $ksSalesInvoiceItem;
        $this->ksInvoiceModel             = $ksInvoiceModel;
        $this->ksConfigurbaleProductModel = $ksConfigurbaleProductModel;
        $this->ksSalesItemModel           = $ksSalesItemModel;
        $this->ksScopeConfig              = $ksScopeConfig;
        $this->ksStoreManager             = $ksStoreManager;
        $this->ksSession                  = $ksSession;
        $this->ksAddksAddressRenderer     = $ksAddressRenderer;
        $this->ksCurrencyFactory          = $ksCurrencyFactory;
        $this->ksRegistry                 = $ksRegistry;
        $this->salesOrder                 = $salesOrder;
        $this->ksWeeeHelper               = $ksWeeeHelper;
    }

    /**
     * @param Order $ksOrder
     */
    public function setKsOrderData($ksOrder)
    {
        $ksOrderItems = $ksOrder->getAllItems();
        $ksSellerIdList =[];
        $ksRate = 1;
        $ksOrderCurrency = $this->ksStoreManager->getStore()->getCurrentCurrency()->getCode();
        $ksBaseCurrency = $this->ksStoreManager->getStore()->getBaseCurrency()->getCode();
        if ($ksOrderCurrency != $ksBaseCurrency) {
            $ksCurrent_currency = $this->ksStoreManager->getStore()->getCurrentCurrencyRate();
            $ksBase_currency = $this->ksStoreManager->getStore()->getBaseCurrency()->getRate($this->ksStoreManager->getStore()->getBaseCurrency());
            $ksRate = $ksCurrent_currency/$ksBase_currency;
        }

        foreach ($ksOrderItems as $ksitem) {
            $ksProduct = $ksitem->getProduct();
            $ksProductId = $ksProduct->getId();
            $ksSellerId = $this->ksProductHelper->getKsSellerId($ksProductId);
            if ($ksSellerId) {
                $ksCommissionRuleList = $this->ksCommissionHelper->getKsCommissionRuleList($ksSellerId, $ksProductId, $this->ksDataHelper->getKsCurrentWebsiteId(), $ksOrder->getStoreId());
                $ksCommissionRuleList->setOrder('ks_created_at', 'DESC');
                $ksBaseCommissionValue = $this->getKsCommissionAmount($ksCommissionRuleList, $ksitem, $ksOrder)*$ksitem->getQtyOrdered();
                /*add commission details if product satisfy any commission rule*/
                $ksBaseProductTotal = 0;
                    
                if (!$this->isKsChildCalculated($ksitem) && $ksitem->getProductType()=="bundle") {
                    $ksChildrenItems = $ksitem->getChildrenItems();

                    foreach ($ksChildrenItems as $ksChildItem) {
                        $ksBaseProductTotal += $this->ksCalcBaseProductTotal($ksChildItem);
                        /*set commission id to 0 as no comission will be available for parent*/
                        $this->ksCurrentCommissionId = 0;
                    }
                } else {
                    $ksBaseProductTotal = $this->ksCalcBaseProductTotal($ksitem);
                }
                
                if ($this->ksCurrentCommissionId) {
                    $ksCommissionValue = $ksBaseCommissionValue * $ksRate;
                    $ksCommission = $this->ksCommissionRule->create()->load($this->ksCurrentCommissionId);
                    $ksData = [
                        'ks_order_id'=>$ksOrder->getId(),
                        'ks_order_increment_id'=>$ksOrder->getIncrementId(),
                        'ks_seller_id'=>$ksSellerId,
                        'ks_sales_order_item_id' => $ksitem->getId(),
                        'ks_product_id' => $ksitem->getProductId(),
                        'ks_commission_type' => $ksCommission->getKsCommissionType(),
                        'ks_commission_rate' => $ksCommission->getKsCommissionValue(),
                        'ks_commission_value'=> $ksCommission->getKsPriceRoundoff() ==1 ? round($ksCommissionValue): $ksCommissionValue,
                        'ks_base_commission_value'=> $ksCommission->getKsPriceRoundoff() ==1 ? round($ksBaseCommissionValue): $ksBaseCommissionValue,
                        'ks_base_weee_tax_applied_row_amnt' => $ksitem->getBaseWeeeTaxAppliedRowAmnt(),
                        'ks_weee_tax_applied_row_amount' => $ksitem->getWeeeTaxAppliedRowAmount(),
                        'ks_qty_ordered'=> $ksitem->getQtyOrdered(),
                        'row_total'=> $ksitem->getRowTotal(),
                        'base_row_total'=> $ksitem->getBaseRowTotal(),
                        'tax_amount'=> $ksitem->getTaxAmount(),
                        'total_tax_amt' => $this->ksCalcItemTaxAmt($ksitem),
                        'base_tax_amount'=> $ksitem->getBaseTaxAmount(),
                        'base_total_tax_amt' => $this->ksCalcItemBaseTaxAmt($ksitem),
                        'discount_amount'=> $ksitem->getDiscountAmount(),
                        'base_discount_amount'=> $ksitem->getBaseDiscountAmount(),
                        'ks_product_type' => $ksitem->getProductType(),
                        'is_child_calculated' => $this->isKsChildCalculated($ksitem),
                        'ks_base_product_total' => $ksBaseProductTotal,
                        'ks_product_total' => $this->ksCalcProductTotal($ksitem)
                    ];
                } else {
                    $ksData = [
                        'ks_order_id'=>$ksOrder->getId(),
                        'ks_order_increment_id'=>$ksOrder->getIncrementId(),
                        'ks_seller_id'=>$ksSellerId,
                        'ks_sales_order_item_id' => $ksitem->getId(),
                        'ks_product_id' => $ksitem->getProductId(),
                        'ks_qty_ordered'=> $ksitem->getQtyOrdered(),
                        'ks_base_weee_tax_applied_row_amnt' => $ksitem->getBaseWeeeTaxAppliedRowAmnt(),
                        'ks_weee_tax_applied_row_amount' => $ksitem->getWeeeTaxAppliedRowAmount(),
                        'row_total'=> $ksitem->getRowTotal(),
                        'base_row_total'=> $ksitem->getBaseRowTotal(),
                        'tax_amount'=> $ksitem->getTaxAmount(),
                        'total_tax_amt' => $this->ksCalcItemTaxAmt($ksitem),
                        'base_tax_amount'=> $ksitem->getBaseTaxAmount(),
                        'base_total_tax_amt' => $this->ksCalcItemBaseTaxAmt($ksitem),
                        'discount_amount'=> $ksitem->getDiscountAmount(),
                        'base_discount_amount'=> $ksitem->getBaseDiscountAmount(),
                        'ks_product_type' => $ksitem->getProductType(),
                        'is_child_calculated' => $this->isKsChildCalculated($ksitem),
                        'ks_base_product_total' => $ksBaseProductTotal,
                        'ks_product_total' => $this->ksCalcProductTotal($ksitem)
                    ];
                }
                $ksSellerIdList[$ksSellerId][] =  $ksData;
            }
        }

        foreach ($ksSellerIdList as $sellerId => $itemList) {
            $base_subtotal = 0;
            $subtotal = 0;
            $base_grandtotal = 0;
            $grandtotal = 0;
            $ksSellerOrderData = array();
            foreach ($itemList as $item) {
                $this->ksSalesOrderItem->setKsOrderItem($item);
                if (($item['ks_product_type']=='bundle' && $item['is_child_calculated']) || $item["ks_product_type"] != 'bundle') {
                    $base_subtotal += $item['base_row_total'] + $item['ks_base_weee_tax_applied_row_amnt'];
                    $subtotal += $item['row_total'] + $item['ks_weee_tax_applied_row_amount'];
                    $base_grandtotal += $item['base_row_total'] + $item['base_total_tax_amt'] + $item['ks_base_weee_tax_applied_row_amnt'] - $item['base_discount_amount'];
                    $grandtotal += $item['row_total'] + $item['total_tax_amt'] + $item['ks_weee_tax_applied_row_amount'] - $item['discount_amount'];
                }
            }

            $ksSellerOrderData = [
            'ks_order_id'=>$ksOrder->getId(),
            'ks_order_increment_id'=>$ksOrder->getIncrementId(),
            'ks_seller_id'=>$sellerId,
            'ks_base_subtotal'=>$base_subtotal,
            'ks_subtotal'=>$subtotal,
            'ks_base_grand_total'=>$base_grandtotal,
            'ks_grand_total'=>$grandtotal
            ];
            $this->ksSalesOrder->setKsOrder($ksSellerOrderData);
            $this->sendKsNewOrderEmail($ksOrder, $sellerId);
        }
    }

    /**
     * Send order email
     *
     * @param Object $ksOrder
     * @param Integer $ksSellerId
     */
    public function sendKsNewOrderEmail($ksOrder, $ksSellerId)
    {
        $ksSender = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_order_settings/ks_send_order_mail');
        if ($this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_order_settings/ks_seller_new_order_email_template') != "disable") {
            $ksSeller = $this->ksDataHelper->getKsCustomerById($ksSellerId);

            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
            $ksEmailVariables = [
                'ks_seller_name' => $ksSeller->getName(),
                'ks_seller_email' =>  $ksSeller->getEmail(),
                'ks_increment_id' => $ksOrder->getIncrementId(),
                'ks_address_details' => $this->ksAddksAddressRenderer->format($ksOrder->getBillingAddress(), 'text'),
                'ks_payment_method' => $this->getKsMethodStoreTitle($ksOrder->getPayment()->getMethod()),
                'ks_shipping_method' => $ksOrder->getShippingDescription()
            ];
            $this->ksEmailHelper->ksSellerNewOrderMail($ksEmailVariables, $ksSenderInfo, $ksEmailVariables['ks_seller_name'], $ksEmailVariables['ks_seller_email']);
        }
    }

    /**
     * Set Invoice Details
     *
     * @param Object $ksInvoice
     */
    public function setKsSellerInvoice($ksInvoice)
    {
        if ($this->getKsInvoiceApprovalFlag()) {
            $this->ksSession->unsKsInvoiceApprovalReq();
            return 0;
        }
        $ksInvoiceItems = $ksInvoice->getAllItems();
        $ksSellersList =[];
        foreach ($ksInvoiceItems as $ksItem) {
            if ($ksSellerId = $this->ksProductHelper->getKsSellerId($ksItem->getProductId())) {
                $ksBaseWeeeTax = ($ksItem->getOrderItem()->getBaseWeeeTaxAppliedRowAmnt()/$ksItem->getOrderItem()->getQtyOrdered())*$ksItem->getQty();
                $ksWeeeTax = ($ksItem->getOrderItem()->getWeeeTaxAppliedRowAmount()/$ksItem->getOrderItem()->getQtyOrdered())*$ksItem->getQty();
                $ksData = [
                    'ks_store_id' => $ksInvoice->getStoreId(),
                    'ks_seller_id'=> $ksSellerId,
                    'ks_approval_required' => 0,
                    'ks_approval_status' =>  $this->ksSalesInvoice::KS_STATUS_APPROVED,
                    'ks_base_grand_total' => $this->ksCanCalculate($ksItem) ? $ksItem->getBaseRowTotal() + ($this->ksCalcItemBaseTaxAmt($ksItem->getOrderItem(), $ksItem->getQty())) + $ksBaseWeeeTax - $ksItem->getBaseDiscountAmount() : 0,
                    'ks_tax_amount' => $ksItem->getTaxAmount(),
                    'ks_base_tax_amount' => $ksItem->getBaseTaxAmount(),
                    'ks_base_total_tax' => $this->ksCalcItemBaseTaxAmt($ksItem->getOrderItem(), $ksItem->getQty()),
                    'ks_total_tax' => $this->ksCalcItemTaxAmt($ksItem->getOrderItem(), $ksItem->getQty()),
                    'ks_grand_total' => $this->ksCanCalculate($ksItem) ? $ksItem->getRowTotal() + ($this->ksCalcItemTaxAmt($ksItem->getOrderItem(), $ksItem->getQty())) + $ksWeeeTax - $ksItem->getDiscountAmount() : 0,
                    'ks_total_qty' => $ksItem->getQty(),
                    'ks_subtotal' => $ksItem->getRowTotal(),
                    'ks_base_subtotal' => $ksItem->getBaseRowTotal(),
                    'ks_discount_amount' => $ksItem->getDiscountAmount(),
                    'ks_base_discount_amount' => $ksItem->getBaseDiscountAmount(),
                    'ks_billing_address_id' => $ksInvoice->getBillingAddressId(),
                    'ks_order_id' => $ksInvoice->getOrderId(),
                    'ks_order_created_at' => $ksInvoice->getOrder()->getCreatedAt(),
                    'ks_order_increment_id' => $ksInvoice->getOrder()->getIncrementId(),
                    'ks_state' => $ksInvoice->getState(),
                    'ks_shipping_address_id' => $ksInvoice->getShippingAddressId(),
                    'ks_transaction_id' => $ksInvoice->getTransactionId(),
                    'ks_customer_note' => $ksInvoice->getCustomerNote(),
                    'ks_comment_customer_notify' => $ksInvoice->getCustomerNoteNotify(),
                    'ks_send_email' => $ksInvoice->getOrder()->getCustomerNoteNotify(),
                    'ks_total_commission' => $this->getKsItemCommission($ksItem->getOrderItemId(), $ksItem->getQty()),
                    'ks_base_total_commission' => $this->getKsItemBaseCommission($ksItem->getOrderItemId(), $ksItem->getQty()),
                    'ks_total_earning' =>$ksItem->getOrderItem()->isDummy() ? 0 : $ksItem->getRowTotal() + ($this->ksCalcItemTaxAmt($ksItem->getOrderItem(), $ksItem->getQty())) + $ksWeeeTax - $ksItem->getDiscountAmount() - $this->getKsItemCommission($ksItem->getOrderItemId(), $ksItem->getQty()),
                    'ks_base_total_earning' => $ksItem->getOrderItem()->isDummy() ? 0 : $ksItem->getBaseRowTotal() + ($this->ksCalcItemBaseTaxAmt($ksItem->getOrderItem(), $ksItem->getQty())) + $ksBaseWeeeTax - $ksItem->getBaseDiscountAmount() - $this->getKsItemBaseCommission($ksItem->getOrderItemId(), $ksItem->getQty()),
                    'ks_invoice_increment_id' => $ksInvoice->getIncrementId()
                ];
                $ksProductType = $ksItem->getOrderItem()->getProductType();
                if (!isset($ksSellersList[$ksSellerId])) {
                    if ($ksProductType!="bundle" || ($ksProductType=="bundle" && $this->isKsChildCalculated($ksItem->getOrderItem()))) {
                        $ksData['ks_base_tax_amount'] = $ksData['ks_base_total_tax'];
                        $ksData['ks_tax_amount'] = $ksData['ks_total_tax'];
                    } else {
                        $ksData['ks_base_tax_amount'] = 0;
                        $ksData['ks_tax_amount'] = 0;
                    }

                    $ksSellersList[$ksSellerId]['invoice'] = $ksData;
                    if ($ksProductType == "configurable") {
                        $ksSellersList[$ksSellerId]['invoice']['ks_total_qty'] = 0;
                    } elseif ($ksProductType == "bundle") {
                        $ksSellersList[$ksSellerId]['invoice']['ks_total_qty'] = 0;
                    }
                    $ksSellersList[$ksSellerId]['items'] = [];
                } else {
                    $ksSellerData = $ksSellersList[$ksSellerId]['invoice'];
                    $ksSellersList[$ksSellerId]['invoice']['ks_base_grand_total'] = $ksSellerData['ks_base_grand_total'] + $ksData['ks_base_grand_total'];
                    $ksSellersList[$ksSellerId]['invoice']['ks_grand_total'] = $ksSellerData['ks_grand_total'] + $ksData['ks_grand_total'];
                    if ($ksProductType!="bundle" || ($ksProductType=="bundle" && $this->isKsChildCalculated($ksItem->getOrderItem()))) {
                        $ksSellersList[$ksSellerId]['invoice']['ks_tax_amount'] = $ksSellerData['ks_tax_amount'] + $ksData['ks_total_tax'];
                        $ksSellersList[$ksSellerId]['invoice']['ks_base_tax_amount'] = $ksSellerData['ks_base_tax_amount'] + $ksData['ks_base_total_tax'];
                    }
                    if ($ksProductType == "configurable") {
                        $ksSellersList[$ksSellerId]['invoice']['ks_total_qty'] = $ksSellerData['ks_total_qty'] + 0;
                    } elseif ($ksProductType == "bundle") {
                        $ksSellersList[$ksSellerId]['invoice']['ks_total_qty'] = $ksSellerData['ks_total_qty'] + 0;
                    } else {
                        $ksSellersList[$ksSellerId]['invoice']['ks_total_qty'] = $ksSellerData['ks_total_qty'] + $ksData['ks_total_qty'];
                    }
                    $ksSellersList[$ksSellerId]['invoice']['ks_subtotal'] = $ksSellerData['ks_subtotal'] + $ksData['ks_subtotal'];
                    $ksSellersList[$ksSellerId]['invoice']['ks_base_subtotal'] = $ksSellerData['ks_base_subtotal'] + $ksData['ks_base_subtotal'];
                    $ksSellersList[$ksSellerId]['invoice']['ks_discount_amount'] = $ksSellerData['ks_discount_amount'] + $ksData['ks_discount_amount'];
                    $ksSellersList[$ksSellerId]['invoice']['ks_base_discount_amount'] = $ksSellerData['ks_base_discount_amount'] + $ksData['ks_base_discount_amount'];
                    $ksSellersList[$ksSellerId]['invoice']['ks_total_commission'] = $ksSellerData['ks_total_commission'] + $ksData['ks_total_commission'];
                    $ksSellersList[$ksSellerId]['invoice']['ks_base_total_commission'] = $ksSellerData['ks_base_total_commission'] + $ksData['ks_base_total_commission'];
                    $ksSellersList[$ksSellerId]['invoice']['ks_total_earning'] = $ksSellerData['ks_total_earning'] + $ksData['ks_total_earning'];
                    $ksSellersList[$ksSellerId]['invoice']['ks_base_total_earning'] = $ksSellerData['ks_base_total_earning'] + $ksData['ks_base_total_earning'];
                }
                array_push($ksSellersList[$ksSellerId]['items'], $ksItem);
            }
        }
        foreach ($ksSellersList as $sellerId => $invoiceDetails) {
            $invoiceDetails['invoice']['ks_base_total_earning'] = $invoiceDetails['invoice']['ks_base_total_earning']>0 ? $invoiceDetails['invoice']['ks_base_total_earning'] : 0;
            $invoiceDetails['invoice']['ks_total_earning'] = $invoiceDetails['invoice']['ks_total_earning']>0 ? $invoiceDetails['invoice']['ks_total_earning'] : 0;
            $ksParentId = $this->ksSalesInvoice->setKsInvoiceIncrReqId($this->ksSalesInvoice->setData($invoiceDetails['invoice'])->save()->getId(), 0);
            foreach ($invoiceDetails['items'] as $invoiceItem) {
                /*set invoice item*/
                $this->ksSalesInvoiceItem->setKsInvoiceItem($invoiceItem, $ksParentId);
                /*increase invoiced quntity for order item*/
                $this->ksSalesOrderItem->setKsQuantityInvoiced($invoiceItem->getOrderItemId(), $invoiceItem->getQty());
            }
        }
    }

    /**
     * Get session flag for invoice approval
     *
     */
    public function getKsInvoiceApprovalFlag()
    {
        return $this->ksSession->getKsInvoiceApprovalReq();
    }

    /**
     * Set Creditmemo Details
     *
     * @param Object $ksCreditmemo
     */
    public function setKsSellerCreditmemo($ksCreditMemo)
    {
        if ($this->getKsMemoApprovalFlag()) {
            $this->ksSession->unsKsMemoApprovalReq();
            return 0;
        }
        $ksCreditmemoItems = $ksCreditMemo->getAllItems();
        $ksSellersList =[];
        foreach ($ksCreditmemoItems as $ksItem) {
            if ($ksSellerId = $this->ksProductHelper->getKsSellerId($ksItem->getProductId())) {
                $ksBaseWeeeTax = ($ksItem->getOrderItem()->getBaseWeeeTaxAppliedRowAmnt()/$ksItem->getOrderItem()->getQtyOrdered())*$ksItem->getQty();
                $ksWeeeTax = ($ksItem->getOrderItem()->getWeeeTaxAppliedRowAmount()/$ksItem->getOrderItem()->getQtyOrdered())*$ksItem->getQty();
                $ksData = [
                    'ks_store_id'=>$ksCreditMemo->getStoreId(),
                    'ks_seller_id'=>$ksSellerId,
                    'ks_approval_required' => 0,
                    'ks_approval_status' => $this->ksSalesCreditMemo::KS_STATUS_APPROVED,
                    'ks_adjustment_positive' => $ksCreditMemo->getAdjustmentPositive(),
                    'ks_base_discount_amount' => $ksItem->getBaseDiscountAmount(),
                    'ks_grand_total' => $this->ksCanCalculate($ksItem) ? $ksItem->getRowTotal() + ($this->ksCalcItemTaxAmt($ksItem->getOrderItem(), $ksItem->getQty())) + $ksWeeeTax - $ksItem->getDiscountAmount() : 0,
                    'ks_base_adjustment_negative' => $ksCreditMemo->getBaseAdjustmentNegative(),
                    'ks_base_subtotal_incl_tax' => $ksItem->getBaseRowTotalInclTax(),
                    'ks_subtotal_incl_tax' => $ksItem->getRowTotalInclTax(),
                    'ks_adjustment_negative' => $ksCreditMemo->getAdjustmentNegative(),
                    'ks_base_adjustment' => $ksCreditMemo->getBaseAdjustment(),
                    'ks_base_subtotal' => $ksItem->getBaseRowTotal(),
                    'ks_discount_amount' => $ksItem->getDiscountAmount(),
                    'ks_subtotal' => $ksItem->getRowTotal(),
                    'ks_adjustment' => $ksCreditMemo->getAdjustment(),
                    'ks_base_grand_total' => $this->ksCanCalculate($ksItem) ? $ksItem->getBaseRowTotal() + ($this->ksCalcItemBaseTaxAmt($ksItem->getOrderItem(), $ksItem->getQty())) + $ksBaseWeeeTax - $ksItem->getBaseDiscountAmount() : 0,
                    'ks_base_adjustment_positive' => $ksCreditMemo->getBaseAdjustmentPositive(),
                    'ks_base_tax_amount' => $ksItem->getBaseTaxAmount(),
                    'ks_tax_amount' => $ksItem->getTaxAmount(),
                    'ks_base_total_tax' => $this->ksCalcItemBaseTaxAmt($ksItem->getOrderItem(), $ksItem->getQty()),
                    'ks_total_tax' => $this->ksCalcItemTaxAmt($ksItem->getOrderItem(), $ksItem->getQty()),
                    'ks_order_id' => $ksCreditMemo->getOrderId(),
                    'ks_order_created_at' => $ksCreditMemo->getOrder()->getCreatedAt(),
                    'ks_order_increment_id' => $ksCreditMemo->getOrder()->getIncrementId(),
                    'ks_creditmemo_status' => $ksCreditMemo->getCreditMemoStatus(),
                    'ks_state' => $ksCreditMemo->getState(),
                    'ks_shipping_address_id' => $ksCreditMemo->getShippingAddressId(),
                    'ks_billing_address_id' => $ksCreditMemo->getBillingAddressId(),
                    'ks_invoice_id' => $ksCreditMemo->getInvoiceId(),
                    'ks_transaction_id' => $ksCreditMemo->getTransactionId(),
                    'ks_discount_description' => $ksCreditMemo->getDiscountDescription(),
                    'ks_customer_note' => $ksCreditMemo->getCustomerNote(),
                    'ks_comment_customer_notify' => $ksCreditMemo->getCustomerNoteNotify(),
                    'ks_send_email' => $ksCreditMemo->getOrder()->getCustomerNoteNotify(),
                    'ks_total_commission' => $this->getKsItemCommission($ksItem->getOrderItemId(), $ksItem->getQty()),
                    'ks_base_total_commission' => $this->getKsItemBaseCommission($ksItem->getOrderItemId(), $ksItem->getQty()),
                    'ks_total_earning' => $ksItem->getOrderItem()->isDummy() ? 0 : $ksItem->getRowTotal() + ($this->ksCalcItemTaxAmt($ksItem->getOrderItem(), $ksItem->getQty())) + $ksWeeeTax - $ksItem->getDiscountAmount() - $this->getKsItemCommission($ksItem->getOrderItemId(), $ksItem->getQty()),
                    'ks_base_total_earning' => $ksItem->getOrderItem()->isDummy() ? 0 : $ksItem->getBaseRowTotal() + ($this->ksCalcItemBaseTaxAmt($ksItem->getOrderItem(), $ksItem->getQty())) + $ksBaseWeeeTax - $ksItem->getBaseDiscountAmount() - $this->getKsItemBaseCommission($ksItem->getOrderItemId(), $ksItem->getQty()),
                    'ks_creditmemo_increment_id' =>$ksCreditMemo->getIncrementId()
                ];
                if (!isset($ksSellersList[$ksSellerId])) {
                    $ksData['ks_base_tax_amount'] = $ksData['ks_base_total_tax'];
                    $ksData['ks_tax_amount'] = $ksData['ks_total_tax'];
                    $ksSellersList[$ksSellerId]['memo'] = $ksData;
                    $ksSellersList[$ksSellerId]['items'] = [];
                } else {
                    $ksSellerData = $ksSellersList[$ksSellerId]['memo'];
                    $ksSellersList[$ksSellerId]['memo']['ks_base_discount_amount'] = $ksSellerData['ks_base_discount_amount'] + $ksData['ks_base_discount_amount'];
                    $ksSellersList[$ksSellerId]['memo']['ks_tax_amount'] = $ksSellerData['ks_tax_amount'] + $ksData['ks_total_tax'];
                    $ksSellersList[$ksSellerId]['memo']['ks_base_tax_amount'] = $ksSellerData['ks_base_tax_amount'] + $ksData['ks_base_total_tax'];
                    $ksSellersList[$ksSellerId]['memo']['ks_grand_total'] = $ksSellerData['ks_grand_total'] + $ksData['ks_grand_total'];
                    $ksSellersList[$ksSellerId]['memo']['ks_base_grand_total'] = $ksSellerData['ks_base_grand_total'] + $ksData['ks_base_grand_total'];
                    $ksSellersList[$ksSellerId]['memo']['ks_base_adjustment_negative'] = $ksSellerData['ks_base_adjustment_negative'] + $ksData['ks_base_adjustment_negative'];
                    $ksSellersList[$ksSellerId]['memo']['ks_base_subtotal'] = $ksSellerData['ks_base_subtotal'] + $ksData['ks_base_subtotal'];
                    $ksSellersList[$ksSellerId]['memo']['ks_subtotal'] = $ksSellerData['ks_subtotal'] + $ksData['ks_subtotal'];
                    $ksSellersList[$ksSellerId]['memo']['ks_discount_amount'] = $ksSellerData['ks_discount_amount'] + $ksData['ks_discount_amount'];
                    $ksSellersList[$ksSellerId]['memo']['ks_total_commission'] = $ksSellerData['ks_total_commission'] + $ksData['ks_total_commission'];
                    $ksSellersList[$ksSellerId]['memo']['ks_total_earning'] = $ksSellerData['ks_total_earning'] + $ksData['ks_total_earning'];
                    $ksSellersList[$ksSellerId]['memo']['ks_base_total_commission'] = $ksSellerData['ks_base_total_commission'] + $ksData['ks_base_total_commission'];
                    $ksSellersList[$ksSellerId]['memo']['ks_base_total_earning'] = $ksSellerData['ks_base_total_earning'] + $ksData['ks_base_total_earning'];
                }
                array_push($ksSellersList[$ksSellerId]['items'], $ksItem);
            }
        }
        foreach ($ksSellersList as $sellerId => $memoDetails) {
            $memoDetails['memo']['ks_total_earning'] = $memoDetails['memo']['ks_total_earning']>0 ? $memoDetails['memo']['ks_total_earning'] : 0;
            $memoDetails['memo']['ks_base_total_earning'] = $memoDetails['memo']['ks_base_total_earning']>0 ? $memoDetails['memo']['ks_base_total_earning'] : 0;
                    
            $ksParentId = $this->ksSalesCreditMemo->setKsCreditMemoIncrReqId($this->ksSalesCreditMemo->setData($memoDetails['memo'])->save()->getId(), 0);
            foreach ($memoDetails['items'] as $memoItem) {
                /*set memo item*/
                $this->ksSalesCreditMemoItem->setKsCreditMemoItem($memoItem, $ksParentId);
                /*increase refunded qauntity for order item*/
                $this->ksSalesOrderItem->setKsQuantityRefunded($memoItem->getOrderItemId(), $memoItem->getQty());
            }
        }
    }

    /**
     * Get session flag for memo approval
     */
    public function getKsMemoApprovalFlag()
    {
        return $this->ksSession->getKsMemoApprovalReq();
    }

    /**
     * Set Shipment Details
     *
     * @param Object $ksShipment
     */
    public function setKsSellerShipment($ksShipment)
    {
        if ($this->getKsShipmentApprovalFlag()) {
            $this->ksSession->unsKsShipmentApprovalReq();
            return 0;
        }

        $ksShipmentItems = $ksShipment->getAllItems();
        $ksTracks = $ksShipment->getAllTracks();
        $ksSellersList =[];
        foreach ($ksShipmentItems as $ksItem) {
            if ($ksSellerId = $this->ksProductHelper->getKsSellerId($ksItem->getProductId())) {
                $ksData = [
                    'ks_store_id' => $ksShipment->getStoreId(),
                    'ks_seller_id' => $ksSellerId,
                    'ks_approval_required' => 0,
                    'ks_approval_status' => $this->ksSalesShipment::KS_STATUS_APPROVED,
                    'ks_total_weight' => 0,
                    'ks_total_qty' => 0,
                    'ks_order_id' => $ksShipment->getOrderId(),
                    'ks_order_created_at' => $ksShipment->getOrder()->getCreatedAt(),
                    'ks_order_increment_id' => $ksShipment->getOrder()->getIncrementId(),
                    'ks_customer_id' => $ksShipment->getCustomerId(),
                    'ks_shipping_address_id' => $ksShipment->getShippingAddressId(),
                    'ks_billing_address_id' => $ksShipment->getBillingAddressId(),
                    'ks_shipment_status' => $ksShipment->getShipmentStatus(),
                    'ks_created_at' => $ksShipment->getCreatedAt(),
                    'ks_updated_at' => $ksShipment->getUpdatedAt(),
                    'ks_shipping_label' => $ksShipment->getShipmentLabel(),
                    'ks_customer_note' => $ksShipment->getCustomerNote(),
                    'ks_comment_customer_notify' => $ksShipment->getCommentCustomerNotify(),
                    'ks_send_email' => $ksShipment->getSendEmail(),
                    'ks_shipment_increment_id' => $ksShipment->getIncrementId()
                ];
                if (!($ksItem->getOrderItem()->getProductType()=='bundle' && $ksItem->getOrderItem()->isShipSeparately())) {
                    $ksData['ks_total_weight'] = $ksItem->getWeight();
                    $ksData['ks_total_qty'] = $ksItem->getQty();
                }
                if (!isset($ksSellersList[$ksSellerId])) {
                    $ksSellersList[$ksSellerId]['shipment'] = $ksData;
                    $ksSellersList[$ksSellerId]['items'] = [];
                } else {
                    $ksSellerData = $ksSellersList[$ksSellerId]['shipment'];
                    if (!($ksItem->getOrderItem()->getProductType()=='bundle' && $ksItem->getOrderItem()->isShipSeparately())) {
                        $ksSellersList[$ksSellerId]['shipment']['ks_total_weight'] = $ksSellerData['ks_total_weight'] + $ksData['ks_total_weight'];
                        $ksSellersList[$ksSellerId]['shipment']['ks_total_qty'] = $ksSellerData['ks_total_qty'] + $ksData['ks_total_qty'];
                    }
                }
                array_push($ksSellersList[$ksSellerId]['items'], $ksItem);
            }
        }
        foreach ($ksSellersList as $sellerId => $shipmentDetails) {
            $ksParentId = $this->ksSalesShipment->setKsShipmentIncrReqId($this->ksSalesShipment->setData($shipmentDetails['shipment'])->save()->getId(), 0);
            foreach ($shipmentDetails['items'] as $shipmentItem) {
                /*set shipment item*/
                $this->ksSalesShipmentItem->setKsShipmentItem($shipmentItem, $ksParentId);
                /*increase shipped quntity for order item*/
                $this->ksSalesOrderItem->setKsQuantityShipped($shipmentItem->getOrderItemId(), $shipmentItem->getQty());
            }
            /*shipment tracks*/
            foreach ($ksTracks as $ksTrack) {
                $this->ksSalesShipmentTrack->setKsTrackingDetails($ksTrack, $ksParentId, $ksTrack->getId());
            }
        }
    }

    /**
     * Get session flag for shipment approval
     *
     */
    public function getKsShipmentApprovalFlag()
    {
        return $this->ksSession->getKsShipmentApprovalReq();
    }

    /**
     * Get ordered product attributes
     *
     * @return array
     */
    public function getKsOrderItemAttrValues($ks_item)
    {
        $result = '';
        if ($this->getOrderOptions($ks_item)) {
            $result .= '<ul class="item-options">';
            foreach ($this->getOrderOptions($ks_item) as $option) {
                $result .= '<li><strong>'.$option['label'].':</strong><br />';
                if (is_array($option['value'])) {
                    foreach ($option['value'] as $item) {
                        $result .= $ks_item.'<br />';
                    }
                } else {
                    $result .= $option['value'];
                }
                $result .= '</li>';
            }
            $result .= '</ul>';
        }
        return $result;
    }

    /**
     * Get order options
     *
     * @return array
     */
    public function getOrderOptions($item)
    {
        $result = [];
        if ($options = $item->getProductOptions()) {
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
     * @param CommissionRuleList $ksCommissionRuleList
     * @param Product            $ksProduct
     * @param Order              $ksOrder
     */
    public function getKsCommissionAmount($ksCommissionRuleList, $ksProduct, $ksOrder)
    {
        $ksProductData = $this->ksProductFactory->create()->setStoreId($ksOrder->getStoreId())->load($ksProduct->getProductId());
        /*check if the product is configurable*/
        if ($ksProduct->getParentItemId() && !in_array($ksProduct->getParentItem()->getProductType(), ['bundle','grouped'])) {
            $this->ksCurrentCommissionId = 0;
            return 0;
        }
        
        $this->ksCurrentPriority = null;
        $this->ksCurrentCommissionId = 0;
        $ksCurrentCommission = 0;
        foreach ($ksCommissionRuleList as $ksRule) {
            if ($this->ksCommissionRule->create()->load($ksRule->getId())->validate($ksProductData)) {
                if ((!is_null($this->ksCurrentPriority)) && !($ksRule->getKsPriority()<$this->ksCurrentPriority)) {
                    continue;
                }
                $ksRuleIsValid = 1;
                if ($ksRule->getKsProductType()) {
                    $ksAllowedProductTypes = explode(',', $ksRule->getKsProductType());
                    $ksRuleIsValid = in_array($ksProductData->getTypeId(), $ksAllowedProductTypes) ? 1 : 0;
                } else {
                    // check for the four product types supported by commission module
                    $ksAllowedProductTypes = ['simple','configurable','virtual','downloadable'];
                    $ksRuleIsValid = in_array($ksProductData->getTypeId(), $ksAllowedProductTypes) ? 1 : 0;
                }
                if ($ksRule->getKsStartDate()) {
                    if (!($ksOrder->getCreatedAt()>$ksRule->getKsStartDate())) {
                        $ksRuleIsValid = 0;
                    }
                }

                if ($ksRule->getKsEndDate()) {
                    if (!($ksOrder->getCreatedAt() < $ksRule->getKsEndDate())) {
                        $ksRuleIsValid = 0;
                    }
                }
                /*check the calculation type*/
                if ($ksRule->getKsCalculationBaseon()==1) {
                    $ksProductPrice = $ksProduct->getBasePrice() + ($this->ksCalcItemBaseTaxAmt($ksProduct)/$ksProduct->getQtyOrdered()) + ($ksProduct->getBaseWeeeTaxAppliedRowAmnt()/$ksProduct->getQtyOrdered());
                    if ($ksRule->getKsMinPrice()) {
                        if (!($ksRule->getKsMinPrice()<$ksProductPrice || $ksRule->getKsMinPrice() == $ksProductPrice)) {
                            $ksRuleIsValid = 0;
                        }
                    }
                    if ($ksRule->getKsMaxPrice()!=0) {
                        if (!($ksRule->getKsMaxPrice()>$ksProductPrice||$ksRule->getKsMaxPrice()==$ksProductPrice)) {
                            $ksRuleIsValid = 0;
                        }
                    }
                    if ($ksRuleIsValid) {
                        $this->ksCurrentCommissionId = $ksRule->getId();
                        $this->ksCurrentPriority = $ksRule->getKsPriority();
                        $ksCurrentCommission = $this->ksCommissionHelper->ksCalcPriceRule($ksRule->getKsCommissionType(), $ksRule->getKsCommissionValue(), $ksProductPrice);
                    }
                } elseif ($ksRule->getKsCalculationBaseon()==2) {
                    if ($ksRule->getKsMinPrice()) {
                        if (!($ksRule->getKsMinPrice()<$ksProduct->getBasePrice() || $ksRule->getKsMinPrice() == $ksProduct->getBasePrice())) {
                            $ksRuleIsValid = 0;
                        }
                    }
                    if ($ksRule->getKsMaxPrice()!=0) {
                        if (!($ksRule->getKsMaxPrice()>$ksProduct->getBasePrice()||$ksRule->getKsMaxPrice()==$ksProduct->getBasePrice())) {
                            $ksRuleIsValid = 0;
                        }
                    }
                    if ($ksRuleIsValid) {
                        $this->ksCurrentCommissionId = $ksRule->getId();
                        $this->ksCurrentPriority = $ksRule->getKsPriority();
                        $ksCurrentCommission = $this->ksCommissionHelper->ksCalcPriceRule($ksRule->getKsCommissionType(), $ksRule->getKsCommissionValue(), $ksProduct->getBasePrice());
                    }
                } elseif ($ksRule->getKsCalculationBaseon()==3) {
                    $ksDiscountAmount = $ksProduct->getBaseDiscountAmount()/$ksProduct->getQtyOrdered();
                    $ksProductPrice = $ksProduct->getBasePrice() + ($this->ksCalcItemBaseTaxAmt($ksProduct)/$ksProduct->getQtyOrdered()) + ($ksProduct->getBaseWeeeTaxAppliedRowAmnt()/$ksProduct->getQtyOrdered()) - $ksDiscountAmount;
                    if ($ksRule->getKsMinPrice()) {
                        if (!($ksRule->getKsMinPrice()<$ksProductPrice || $ksRule->getKsMinPrice() == $ksProductPrice)) {
                            $ksRuleIsValid = 0;
                        }
                    }

                    if ($ksRule->getKsMaxPrice()!=0) {
                        if (!($ksRule->getKsMaxPrice()>$ksProductPrice||$ksRule->getKsMaxPrice()==$ksProductPrice)) {
                            $ksRuleIsValid = 0;
                        }
                    }
                    if ($ksRuleIsValid) {
                        $this->ksCurrentCommissionId = $ksRule->getId();
                        $this->ksCurrentPriority = $ksRule->getKsPriority();
                        $ksCurrentCommission = $this->ksCommissionHelper->ksCalcPriceRule($ksRule->getKsCommissionType(), $ksRule->getKsCommissionValue(), $ksProductPrice);
                    }
                } elseif ($ksRule->getKsCalculationBaseon()==4) {
                    $ksDiscount_amount = $ksProduct->getBaseDiscountAmount()/$ksProduct->getQtyOrdered();
                    $ksProductPrice = ($ksProduct->getBasePrice() - $ksDiscount_amount);
                    if ($ksRule->getKsMinPrice()) {
                        if (!($ksRule->getKsMinPrice()<$ksProductPrice || $ksRule->getKsMinPrice() == $ksProductPrice)) {
                            $ksRuleIsValid = 0;
                        }
                    }

                    if ($ksRule->getKsMaxPrice()!=0) {
                        if (!($ksRule->getKsMaxPrice()>$ksProductPrice||$ksRule->getKsMaxPrice()==$ksProductPrice)) {
                            $ksRuleIsValid = 0;
                        }
                    }
                    if ($ksRuleIsValid) {
                        $this->ksCurrentCommissionId = $ksRule->getId();
                        $this->ksCurrentPriority = $ksRule->getKsPriority();
                        $ksCurrentCommission = $this->ksCommissionHelper->ksCalcPriceRule($ksRule->getKsCommissionType(), $ksRule->getKsCommissionValue(), $ksProductPrice);
                    }
                }
            }
        }
        return $ksCurrentCommission;
    }

    /**
     * @param integer $ksIncrementId
     * @return integer
     */
    public function getKsTotalOrderCommission($ksIncrementId)
    {
        $ksOrderItems = $this->ksSalesOrderItem->getCollection()->addFieldToFilter('ks_order_increment_id', $ksIncrementId);
        $ksTotalCommission = 0;
        foreach ($ksOrderItems as $ksItem) {
            $ksTotalCommission += $ksItem->getKsCommissionValue();
        }
        return $ksTotalCommission;
    }

    /**
     * @param integer $ksIncrementId
     * @return integer
     */
    public function getKsAdminTotalBaseOrderCommission($ksIncrementId)
    {
        $ksOrderItems = $this->ksSalesOrderItem->getCollection()->addFieldToFilter('ks_order_increment_id', $ksIncrementId);
        $ksTotalCommission = 0;
        foreach ($ksOrderItems as $ksItem) {
            $ksTotalCommission += $ksItem->getKsBaseCommissionValue();
        }
        return $ksTotalCommission;
    }

    /**
     * @param integer $ksIncrementId
     * @return integer
     */
    public function getKsSellerTotalBaseOrderCommission($ksIncrementId)
    {
        $ksOrderItems = $this->ksSalesOrderItem->getCollection()->addFieldToFilter('ks_order_increment_id', $ksIncrementId);
        if ($this->ksSellerHelper->getKsCustomerId()) {
            $ksOrderItems->addFieldToFilter('ks_seller_id', $this->ksSellerHelper->getKsCustomerId());
        }
        $ksTotalCommission = 0;
        foreach ($ksOrderItems as $ksItem) {
            $ksTotalCommission += $ksItem->getKsBaseCommissionValue();
        }
        return $ksTotalCommission;
    }

    /**
     * @param integer $ksIncrementId
     * @return integer
     */
    public function getKsSellerTotalOrderCommission($ksIncrementId)
    {
        $ksOrderItems = $this->ksSalesOrderItem->getCollection()->addFieldToFilter('ks_order_increment_id', $ksIncrementId)->addFieldToFilter('ks_seller_id', $this->ksSellerHelper->getKsCustomerId());
        $ksTotalCommission = 0;
        foreach ($ksOrderItems as $ksItem) {
            $ksTotalCommission += $ksItem->getKsCommissionValue();
        }
        return $ksTotalCommission;
    }

    /**
     * @param integer $ksItemId
     * @param integer $ksQty
     * @return integer
     */
    public function getKsItemBaseCommission($ksItemId, $ksQty)
    {
        $ksCommissionRate=0;
        $ksOrderItem = $this->ksSalesOrderItem->load($ksItemId, 'ks_sales_order_item_id');
        $ksTotalCommission = $ksOrderItem->getKsBaseCommissionValue();
        $ksOrderQty = $ksOrderItem->getKsQtyOrdered();
        if ($ksOrderQty) {
            $ksCommissionRate = $ksOrderItem->getKsBaseCommissionValue() / $ksOrderQty ;
        } else {
            $ksTotalCommission =0;
        }
        if ($ksQty==0) {
            $ksTotalCommission = 0;
        } else {
            $ksTotalCommission = $ksCommissionRate*$ksQty;
        }
        return $ksTotalCommission;
    }

    /**
     * @param integer $ksItemId
     * @param integer $ksQty
     * @return integer
     */
    public function getKsItemCommission($ksItemId, $ksQty)
    {
        $ksCommissionRate=0;
        $ksOrderItem = $this->ksSalesOrderItem->load($ksItemId, 'ks_sales_order_item_id');
        $ksTotalCommission = $ksOrderItem->getKsCommissionValue();
        $ksOrderQty = $ksOrderItem->getKsQtyOrdered();
        if ($ksOrderQty) {
            $ksCommissionRate = $ksOrderItem->getKsCommissionValue() / $ksOrderQty ;
        } else {
            $ksTotalCommission =0;
        }
        if ($ksQty==0) {
            $ksTotalCommission = 0;
        } else {
            $ksTotalCommission = $ksCommissionRate*$ksQty;
        }
        return $ksTotalCommission;
    }

    /**
     * Check if order is of currently logged in seller
     *
     * @param integer $ksOrderId
     * @return integer
     */
    public function ksIsSellerOrder($ksOrderId)
    {
        $ksSellerOrders = $this->ksSalesOrder->getCollection()->addFieldToFilter('ks_seller_id', $this->ksDataHelper->getKsCustomerId())->addFieldToFilter('ks_order_id', $ksOrderId);
        return $ksSellerOrders->getSize();
    }

    /**
     * Check the seller product item
     * @param $productId
     * @return bool
     */
    public function KsIsSellerProduct($ks_productId)
    {
        $ks_collection = $this->ksProduct->create()->getCollection()
        ->addFieldToFilter('ks_product_id', $ks_productId)
        ->addFieldToFilter('ks_seller_id', $this->ksSellerHelper->getKsCustomerId());
        if ($ks_collection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Convert and format price value for current application store
     *
     * @param   float $value
     * @param   bool $format
     * @param   bool $includeContainer
     * @return  float|string
     */
    public function KsCurrencyHelper($value, $format = true, $includeContainer = true)
    {
        return $this->ksPriceHelper->currency($value, $format = true, $includeContainer = true);
    }

    /**
     * @param integer $ksShipmentId KsSalesShipment
     * @return Boolean
     */
    public function getShipmentApprovalStatus($ksShipmentId)
    {
        return $this->ksSalesShipment->load($ksShipmentId)->getKsApprovalStatus() == 1;
    }

    /**
     * @param integer $ksShipmentId KsSalesShipment
     * @return integer
     */
    public function getShipmentId($ksShipmentId)
    {
        $ksIncId = $this->ksSalesShipment->load($ksShipmentId)->getKsShipmentIncrementId();
        return $this->ksShipmentModel->loadByIncrementId($ksIncId)->getId();
    }

    /**
     * @param integer $ksShipmentId KsSalesShipment
     * @return integer
     */
    public function getKsOrderId($ksShipmentId)
    {
        return $this->ksSalesShipment->load($ksShipmentId)->getKsOrderId();
    }

    /**
     * @param integer $ksInvoiceId KsSalesInvoice
     * @return integer
     */
    public function getInvoiceId($ksInvoiceId)
    {
        $ksIncId = $this->ksSalesInvoice->load($ksInvoiceId)->getKsInvoiceIncrementId();
        return $this->ksInvoiceModel->loadByIncrementId($ksIncId)->getId();
    }
  
    /**
     * @param integer $ksCreditMemoId KsSalesCreditMemo
     * @return integer
     */
    public function getCreditMemoId($ksCreditMemoId)
    {
        $ksIncId = $this->ksSalesCreditMemo->load($ksCreditMemoId)->getKsCreditmemoIncrementId();
        return $this->ksCreditMemoModel->getCollection()->addFieldToFilter('increment_id', $ksIncId)->getFirstItem()->getId();
    }

    /**
     * @param integer $ksOrderItemId
     * @return integer
     */
    public function getKsInvoicedQty($ksOrderItemId)
    {
        return $this->ksSalesOrderItem->load($ksOrderItemId, 'ks_sales_order_item_id')->getKsQtyInvoiced();
    }

    /**
     * @param integer $ksOrderItemId
     * @return integer
     */
    public function getKsInvoiceableQty($ksOrderItemId)
    {
        return $this->ksSalesOrderItem->load($ksOrderItemId, 'ks_sales_order_item_id')->getKsQtyToInvoice();
    }

    /**
     * @param integer $ksOrderItemId
     * @return integer
     */
    public function getKsShippedQty($ksOrderItemId)
    {
        return $this->ksSalesOrderItem->getCollection()->addFieldToFilter('ks_sales_order_item_id', $ksOrderItemId)->getFirstItem()->getKsQtyShipped();
    }

    /**
     * @param integer $ksOrderItemId
     * @return integer
     */
    public function getKsRefundedQty($ksOrderItemId)
    {
        return $this->ksSalesOrderItem->getCollection()->addFieldToFilter('ks_sales_order_item_id', $ksOrderItemId)->getFirstItem()->getKsQtyRefunded();
    }

    /**
     * @param integer $ksOrderItemId
     * @return integer
     */
    public function getKsRefundableQty($ksOrderItemId)
    {
        return $this->ksSalesOrderItem->loadByKsOrderItemId($ksOrderItemId)->getKsQtyToRefund();
    }

    /**
     * @param integer $ksProductId
     * @return Boolean
     */
    public function getKsParentItemId($ksOrderItemId)
    {
        if ($this->ksSalesItemModel->get($ksOrderItemId)->getParentItem()) {
            return $this->ksSalesItemModel->get($ksOrderItemId)->getId();
        }
        return 0;
    }

    /**
     * @return bool
     */
    public function isKsSellerAllowed()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_minimum_order_amount_settings/ks_minimum_order_amount_settings_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        ) && $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_minimum_order_amount_settings/ks_allow_seller_set_minimum_order_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return string
     */
    public function checkNotesAndNotify()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_order_settings/ks_allow_comments',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return Boolean
     */
    public function checkNotesAndNotifyInvoice()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_invoice_settings/ks_allow_comments',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return Boolean
     */
    public function canKsSendInvoiceEmail()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_invoice_settings/ks_send_invoice_copy',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return Boolean
     */
    public function checkNotesAndNotifyCreditMemo()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_creditmemo_settings/ks_allow_comments',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return Boolean
     */
    public function canKsSendMemoEmail()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_creditmemo_settings/ks_send_creditmemo_copy',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return Boolean
     */
    public function checkNotesAndNotifyShipment()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_shipment_settings/ks_allow_comments',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     *
     * @param $ksOrderId Order Id
     * @return Integer Increment Id
     */
    public function getKsOrderIncrementId($ksOrderId)
    {
        return $this->ksSalesOrder->getCollection()->addFieldToFilter('ks_order_id', $ksOrderId)->getFirstItem()->getKsOrderIncrementId();
    }

    /**
     * @param integer $ksInvoiceIncrId
     * @return Boolean | Integer
     */
    public function getKsIsSellerInvoice($ksInvoiceIncrId)
    {
        $ksInvoice = $this->ksSalesInvoice->getCollection()->addFieldToFilter('ks_invoice_increment_id', $ksInvoiceIncrId)->getFirstItem();
        if ($ksInvoice) {
            return $ksInvoice->getKsSellerId();
        }
        return false;
    }

    /**
     * @param integer $ksShipmentIncrId
     * @return Boolean | Integer
     */
    public function getKsIsSellerShipment($ksShipmentIncrId)
    {
        $ksShipment = $this->ksSalesShipment->getCollection()->addFieldToFilter('ks_shipment_increment_id', $ksShipmentIncrId)->getFirstItem();
        if ($ksShipment) {
            return $ksShipment->getKsSellerId();
        }
        return false;
    }

    /**
     * @param integer $ksMemoIncrId
     * @return Boolean | Integer
     */
    public function getKsIsSellerMemo($ksMemoIncrId)
    {
        $ksMemo = $this->ksSalesCreditMemo->getCollection()->addFieldToFilter('ks_creditmemo_increment_id', $ksMemoIncrId)->getFirstItem();
        if ($ksMemo) {
            return $ksMemo->getKsSellerId();
        }
        return false;
    }

    /**
     * Get config title of payment method
     *
     * @param string $code
     * @param int|null $storeId
     * @return string
     */
    public function getKsMethodStoreTitle($code, $storeId = null)
    {
        $configPath = sprintf('%s/%s/title', self::XML_PATH_PAYMENT_METHODS, $code);
        return (string) $this->ksDataHelper->getKsConfigValue(
            $configPath,
            $storeId
        );
    }

    /**
     * @return object
     */
    public function getKsOrder()
    {
        return $this->ksRegistry->registry('current_order');
    }

    /**
     * Format total value based on base currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatValue($price, $precision = 2)
    {
        return $this->salesOrder->getBaseCurrency()->formatPrecision($price, $precision);
    }

    /**
     * Format total value based on order currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatToOrderCurrency($price)
    {
        return $this->salesOrder->formatPrice($price, true);
    }

    /**
     * Format total value based on order currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function canKsShowOrderCurrencyValue()
    {
        return !($this->salesOrder->getOrderCurrencyCode() == $this->salesOrder->getStoreCurrencyCode());
    }

    /**
     * Get ordered product attribute
     *
     * @return array
     */
    public function getKsOrderItemAttrValue($ks_item)
    {
        $result = '';
        if ($this->getOrderOptions($ks_item)) {
            foreach ($this->getOrderOptions($ks_item) as $option) {
                $result .= $option['label'].': ';
                if (is_array($option['value'])) {
                    foreach ($option['value'] as $item) {
                        $result .= $ks_item;
                    }
                } else {
                    $result .= $option['value'];
                }
            }
        }
        return $result;
    }

    /**
     * Get ordered product attribute
     *
     * @return array
     */
    public function getKsCreditMemoItemAttrValue($ks_item)
    {
        $result = '';
        if ($this->getOrderOptions($ks_item)) {
            foreach ($this->getOrderOptions($ks_item) as $option) {
                $result .= $option['label'].': ';
                if (is_array($option['value'])) {
                    foreach ($option['value'] as $item) {
                        $result .= $ks_item;
                    }
                } else {
                    $result .= $option['value'];
                }
            }
        }
        return $result;
    }

    /**
     * Get ordered product attribute
     *
     * @return array
     */
    public function getKsShipmentItemAttrValue($ks_item)
    {
        $result = '';
        if ($this->getOrderOptions($ks_item)) {
            foreach ($this->getOrderOptions($ks_item) as $option) {
                $result .= $option['label'].': ';
                if (is_array($option['value'])) {
                    foreach ($option['value'] as $item) {
                        $result .= $ks_item;
                    }
                } else {
                    $result .= $option['value'];
                }
            }
        }
        return $result;
    }

    /**
     * Get invoice item
     *
     * @param $ksInvoiceIncrId Integer
     * @param $ksOrderItemId Integer
     * @return Magento\Sales\Model\Order\Invoice\Item | Boolean
     */
    public function getKsSalesInvoiceItem($ksInvoiceIncrId, $ksOrderItemId)
    {
        $ksItems = $this->ksInvoiceModel->loadByIncrementId($ksInvoiceIncrId)->getAllItems();
        foreach ($ksItems as $ksItem) {
            if ($ksItem->getOrderItemId() == $ksOrderItemId) {
                return $ksItem;
            }
        }
        return false;
    }

    /**
     * Get invoice request item
     *
     * @param $ksReqId Integer
     * @param $ksOrderItemId Integer
     * @return \Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceItem
     */
    public function getKsSalesInvoiceReqItem($ksReqId, $ksOrderItemId)
    {
        return $this->ksSalesInvoiceItem->getCollection()->addFieldToFilter('ks_parent_id', $ksReqId)->addFieldToFilter('ks_order_item_id', $ksOrderItemId)->getFirstItem();
    }

    /**
     * Get Shipment Item
     *
     * @param $ksShipmentIncrId Integer
     * @param $ksOrderItemId Integer
     * @return Magento\Sales\Model\Order\Shipment\Item
     *
     */
    public function getKsSalesShipmentItem($ksShipmentIncrId, $ksOrderItemId)
    {
        $ksItems = $this->ksShipmentModel->loadByIncrementId($ksShipmentIncrId)->getAllItems();
        foreach ($ksItems as $ksItem) {
            if ($ksItem->getOrderItemId() == $ksOrderItemId) {
                return $ksItem;
            }
        }
        return false;
    }

    /**
     * Get Shipment request item
     *
     * @param $ksReqId Integer
     * @param $ksOrderItemId Integer
     * @return \Ksolves\MultivendorMarketplace\Model\KsSalesShipmentItem
     */
    public function getKsSalesShipmentReqItem($ksReqId, $ksOrderItemId)
    {
        return $this->ksSalesShipmentItem->getCollection()->addFieldToFilter('ks_parent_id', $ksReqId)->addFieldToFilter('ks_order_item_id', $ksOrderItemId)->getFirstItem();
    }

    /**
     * Get Memo Item
     *
     * @param $ksMemoIncrId Integer
     * @param $ksOrderItemId Integer
     * @return Magento\Sales\Model\Order\Creditmemo\Item
     */
    public function getKsSalesMemoItem($ksMemoIncrId, $ksOrderItemId)
    {
        $ksItems = $this->ksCreditMemoModel->load($ksMemoIncrId, 'increment_id')->getAllItems();
        foreach ($ksItems as $ksItem) {
            if ($ksItem->getOrderItemId() == $ksOrderItemId) {
                return $ksItem;
            }
        }
        return false;
    }

    /**
     * Get Memo request item
     *
     * @param $ksReqId Integer
     * @param $ksOrderItemId Integer
     * @return \Ksolves\MultivendorMarketplace\Model\KsSalesCreditmemoItem
     */
    public function getKsSalesMemoReqItem($ksReqId, $ksOrderItemId)
    {
        return $this->ksSalesCreditMemoItem->getCollection()->addFieldToFilter('ks_parent_id', $ksReqId)->addFieldToFilter('ks_order_item_id', $ksOrderItemId)->getFirstItem();
    }

    /**
     * Check if child items calculated
     *
     * @param mixed $ksItem
     * @return bool
     */
    public function isKsChildCalculated($ksItem = null)
    {
        $options = $ksItem->getProductOptions();
        if ($options) {
            if (isset($options['product_calculations'])
                && $options['product_calculations'] == AbstractType::CALCULATE_PARENT
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if order contains any seller's order
     *
     * @param integer $ksOrderId
     * @return integer
     */
    public function ksIsMarketplaceOrder($ksOrderId)
    {
        $ksSellerOrders = $this->ksSalesOrder->getCollection()->addFieldToFilter('ks_order_id', $ksOrderId);
        return $ksSellerOrders->getSize();
    }

    /**
     * Calculate base item tax
     *
     * @param Object $ksItem
     * @param Integer $ksQty=null
     */
    public function ksCalcItemBaseTaxAmt($ksItem, $ksQty=null)
    {
        $ksAmt = $ksItem->getBaseTaxAmount();

        if ($ksWeeeTax = $this->ksWeeeHelper->getApplied($ksItem)) {
            foreach ($ksWeeeTax as $ksTax) {
                $ksAmt += ($ksTax['base_row_amount_incl_tax']-$ksTax['base_row_amount']);
            }
        }

        if ($ksQty) {
            $ksAmt = ($ksAmt/$ksItem->getQtyOrdered())*$ksQty;
        }

        return $ksAmt;
    }

    /**
     * Calculate item tax
     *
     * @param Object $ksItem
     * @param Integer $ksQty=null
     */
    public function ksCalcItemTaxAmt($ksItem, $ksQty=null)
    {
        $ksAmt = $ksItem->getTaxAmount();

        if ($ksWeeeTax = $this->ksWeeeHelper->getApplied($ksItem)) {
            foreach ($ksWeeeTax as $ksTax) {
                $ksAmt += ($ksTax['row_amount_incl_tax']-$ksTax['row_amount']);
            }
        }

        if ($ksQty) {
            $ksAmt = ($ksAmt/$ksItem->getQtyOrdered())*$ksQty;
        }

        return $ksAmt;
    }

    /**
     * Fetch Base Invoiced Amount of order
     *
     * @param $ksOrderId
     */
    public function getKsBaseOrderPaidAmt($ksOrderId)
    {
        $ksInvoices = $this->ksSalesInvoice->getCollection()->addFieldToFilter('ks_order_id', $ksOrderId)->addFieldToFilter('ks_approval_status', $this->ksSalesInvoice::KS_STATUS_APPROVED)->addFieldToFilter('ks_seller_id', $this->ksDataHelper->getKsCustomerId());
        $ksTotalPaid = 0;

        foreach ($ksInvoices as $ksInvoice) {
            $ksTotalPaid += $ksInvoice->getKsBaseGrandTotal();
        }

        return $ksTotalPaid;
    }

    /**
     * Fetch Ordered Invoiced Amount of order
     *
     * @param $ksOrderId
     */
    public function getKsOrderPaidAmt($ksOrderId)
    {
        $ksInvoices = $this->ksSalesInvoice->getCollection()->addFieldToFilter('ks_order_id', $ksOrderId)->addFieldToFilter('ks_approval_status', $this->ksSalesInvoice::KS_STATUS_APPROVED)->addFieldToFilter('ks_seller_id', $this->ksDataHelper->getKsCustomerId());
        $ksTotalPaid = 0;

        foreach ($ksInvoices as $ksInvoice) {
            $ksTotalPaid += $ksInvoice->getKsGrandTotal();
        }

        return $ksTotalPaid;
    }

    /**
     * Fetch Base Refunded Amount of order
     *
     * @param $ksOrderId
     */
    public function getKsBaseOrderRefundedAmt($ksOrderId)
    {
        $ksMemos = $this->ksSalesCreditMemo->getCollection()->addFieldToFilter('ks_order_id', $ksOrderId)->addFieldToFilter('ks_approval_status', $this->ksSalesCreditMemo::KS_STATUS_APPROVED)->addFieldToFilter('ks_seller_id', $this->ksDataHelper->getKsCustomerId());
        $ksTotalPaid = 0;

        foreach ($ksMemos as $ksMemo) {
            $ksTotalPaid += $ksMemo->getKsBaseGrandTotal();
        }

        return $ksTotalPaid;
    }

    /**
     * Fetch Ordered Refunded Amount of order
     *
     * @param $ksOrderId
     */
    public function getKsOrderRefundedAmt($ksOrderId)
    {
        $ksMemos = $this->ksSalesCreditMemo->getCollection()->addFieldToFilter('ks_order_id', $ksOrderId)->addFieldToFilter('ks_approval_status', $this->ksSalesCreditMemo::KS_STATUS_APPROVED)->addFieldToFilter('ks_seller_id', $this->ksDataHelper->getKsCustomerId());
        $ksTotalPaid = 0;

        foreach ($ksMemos as $ksMemo) {
            $ksTotalPaid += $ksMemo->getKsGrandTotal();
        }

        return $ksTotalPaid;
    }

    /**
     * Check if can include in item calculation
     *
     * @param $ksItem
     */
    public function ksCanCalculate($ksItem)
    {
        return ($ksItem->getOrderItem()->getProductType() =='bundle' && $this->isKsChildCalculated($ksItem->getOrderItem())) || $ksItem->getOrderItem()->getProductType() != 'bundle';
    }

    /**
     * Calculate Base Product Total
     *
     * @param Object $ksItem
     * @return Integer
     */
    public function ksCalcBaseProductTotal($ksItem)
    {
        return $ksItem->getBaseRowTotal() + $ksItem->getBaseWeeeTaxAppliedRowAmnt() + $this->ksCalcItemBaseTaxAmt($ksItem) - $ksItem->getBaseDiscountAmount();
    }

    /**
     * Calculate Product Total
     *
     * @param Object $ksItem
     * @return Integer
     */
    public function ksCalcProductTotal($ksItem)
    {
        return $ksItem->getRowTotal() + $ksItem->getWeeeTaxAppliedRowAmount() + $this->ksCalcItemTaxAmt($ksItem) - $ksItem->getDiscountAmount();
    }
}
