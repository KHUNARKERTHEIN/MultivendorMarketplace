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

namespace Ksolves\MultivendorMarketplace\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context as ModelContext;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * KsSalesInvoice Model Class
 */
class KsSalesInvoice extends AbstractModel
{

    /**
     * Invoice Approval Statuses
     */
    const KS_STATUS_PENDING     = 0;
    const KS_STATUS_APPROVED    = 1;
    const KS_STATUS_REJECTED    = 2;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesInvoice');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_sales_invoice';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_sales_invoice';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_sales_invoice';

    /**
     * @var KsSalesInvoiceItemFactory
     */
    protected $ksSalesInvoiceItemFactory;

    /**
     * @var KsSalesOrderItemFactory
     */
    protected $ksSalesOrderItemFactory;

    /**
     * Prepare Order Approval statuses
     *
     * @return array
     */
    public function getKsApprovalStatuses()
    {
        return [
            self::KS_STATUS_PENDING      => __('Pending'),
            self::KS_STATUS_APPROVED     => __('Approved'),
            self::KS_STATUS_REJECTED     => __('Rejected')
        ];
    }

    /**
     * @param ModelContext $ksContext
     * @param Registry $ksRegistry
     * @param KsSalesInvoiceItemFactory $ksSalesInvoiceItemFactory
     * @param KsSalesOrderItemFactory $ksSalesOrderItemFactory
     * @param AbstractResource|null $ksResource
     * @param AbstractDb|null $ksResourceCollection
     * @param array $ksData
     */
    public function __construct(
        ModelContext $ksContext,
        Registry $ksRegistry,
        KsSalesInvoiceItemFactory $ksSalesInvoiceItemFactory,
        KsSalesOrderItemFactory $ksSalesOrderItemFactory,
        AbstractResource $ksResource = null,
        AbstractDb $ksResourceCollection = null,
        array $ksData = []
    ) {
        $this->ksSalesOrderItemFactory = $ksSalesOrderItemFactory;
        $this->ksSalesInvoiceItemFactory = $ksSalesInvoiceItemFactory;
        parent::__construct($ksContext, $ksRegistry, $ksResource, $ksResourceCollection, $ksData);
    }

    /**
     * Set Invoice Details
     *
     * @param Object $ksOrder
     * @param Int    $ksSellerId
     */
    public function setKsInvoice($ksInvoice, $ksSellerId, $ksApprovalReq, $invoiceData, $invoiceIncrementId=0)
    {
        $ksData = [
            'ks_store_id' => $ksInvoice->getStoreId(),
            'ks_seller_id'=> $ksSellerId,
            'ks_approval_required' => $ksApprovalReq,
            'ks_approval_status' => $ksApprovalReq==1 ? self::KS_STATUS_PENDING : self::KS_STATUS_APPROVED,
            'ks_base_grand_total' => $ksInvoice->getBaseGrandTotal() - $ksInvoice->getBaseShippingAmount() - $ksInvoice->getBaseShippingTaxAmount() + ($ksInvoice->getBaseShippingAmount()? $ksInvoice->getOrder()->getBaseShippingDiscountAmount():0),
            'ks_tax_amount' => $ksInvoice->getTaxAmount() - $ksInvoice->getShippingTaxAmount(),
            'ks_base_tax_amount' => $ksInvoice->getBaseTaxAmount() - $ksInvoice->getBaseShippingTaxAmount(),
            'ks_grand_total' => $ksInvoice->getGrandTotal() - $ksInvoice->getShippingAmount() - $ksInvoice->getShippingTaxAmount() + ($ksInvoice->getShippingAmount()? $ksInvoice->getOrder()->getShippingDiscountAmount():0),
            'ks_total_qty' => $invoiceData['ks_total_qty'],
            'ks_subtotal' => $ksInvoice->getSubtotal(),
            'ks_base_subtotal' => $ksInvoice->getBaseSubtotal(),
            'ks_discount_amount' => $ksInvoice->getDiscountAmount() + ($ksInvoice->getShippingAmount()? $ksInvoice->getOrder()->getShippingDiscountAmount():0),
            'ks_base_discount_amount' => $ksInvoice->getBaseDiscountAmount() + ($ksInvoice->getBaseShippingAmount()? $ksInvoice->getOrder()->getBaseShippingDiscountAmount():0),
            'ks_billing_address_id' => $ksInvoice->getBillingAddressId(),
            'ks_order_id' => $ksInvoice->getOrderId(),
            'ks_state' => $ksInvoice->getState(),
            'ks_shipping_address_id' => $ksInvoice->getShippingAddressId(),
            'ks_transaction_id' => $ksInvoice->getTransactionId(),
            'ks_customer_note' => $ksInvoice->getCustomerNote(),
            'ks_order_created_at' => $ksInvoice->getOrder()->getCreatedAt(),
            'ks_order_increment_id' => $ksInvoice->getOrder()->getIncrementId(),
            'ks_comment_customer_notify' => isset($invoiceData['comment_customer_notify']),
            'ks_send_email' => isset($invoiceData['send_email']),
            'ks_base_total_commission' => $invoiceData['ks_base_total_commission'],
            'ks_total_commission' => $invoiceData['ks_total_commission'],
            'ks_total_earning' => $invoiceData['ks_total_earning'],
            'ks_base_total_earning' => $invoiceData['ks_base_total_earning']
        ];
        if ($invoiceIncrementId) {
            $ksData['ks_invoice_increment_id'] = $invoiceIncrementId;
        }
        return $this->setKsInvoiceIncrReqId($this->setData($ksData)->save()->getId(), $ksApprovalReq);
    }

    /**
     * Set Invoice Increment Id
     *
     * @param Object $ksOrder
     * @param Int    $ksSellerId
     */
    public function setKsInvoiceIncrReqId($ksInvoiceId, $ksApprovalReq)
    {
        $ksInvoice = $this->load($ksInvoiceId);
        $ksReqId = 'REQ'.sprintf("%09d", $ksInvoiceId);
        if ($ksApprovalReq) {
            $ksInvoice->setKsInvoiceIncrementId($ksReqId);
            $ksInvoice->setKsRequestIncrementId($ksReqId);
        } else {
            $ksInvoice->setKsRequestIncrementId($ksReqId);
        }
        return $ksInvoice->save()->getId();
    }

    /**
     * @param false $reload
     * @return mixed
     */
    public function getCommentsCollection($reload = false)
    {
        if (!$this->hasData(InvoiceInterface::COMMENTS) || $reload) {
            $comments = $this->_commentCollectionFactory->create()->setInvoiceFilter($this->getId())
                ->setCreatedAtOrder();

            $this->setComments($comments);
            /**
             * When invoice created with adding comment, comments collection
             * must be loaded before we added this comment.
             */
            $this->getComments()->load();

            if ($this->getId()) {
                foreach ($this->getComments() as $comment) {
                    $comment->setInvoice($this);
                }
            }
        }
        return $this->getComments();
    }

    /**
     * @return $this|KsSalesInvoice
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        if (!$this->_actionValidator->isAllowed($this)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Delete operation is forbidden for current area')
            );
        }

        $ksInvoiceItemData =$this->ksSalesInvoiceItemFactory->create()->getCollection()
            ->addFieldToFilter('ks_parent_id', $this->getId());

        if ($this->getKsApprovalStatus() != self::KS_STATUS_APPROVED) {
            foreach ($ksInvoiceItemData as $ksInvoiceItem) {
                $ksInvoiceQty = $ksInvoiceItem->getKsQty();
                $ksSalesOrderItem = $this->ksSalesOrderItemFactory->create()
                    ->load($ksInvoiceItem->getKsOrderItemId(), 'ks_sales_order_item_id');
                $ksSalesOrderItem->setKsQtyInvoiced($ksSalesOrderItem->getKsQtyInvoiced() - $ksInvoiceQty);
                $ksSalesOrderItem->save();
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('You are not authorised to delete this entity.')
            );
        }

        $this->_eventManager->dispatch('model_delete_before', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_delete_before', $this->_getEventData());
        $this->cleanModelCache();
        return $this;
    }
}
