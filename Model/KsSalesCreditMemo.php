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

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context as ModelContext;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * KsSalesCreditMemo Model Class
 */
class KsSalesCreditMemo extends AbstractModel
{

    /**
     * Credit Memo Approval Statuses
     */
    const KS_STATUS_PENDING     = 0;
    const KS_STATUS_APPROVED    = 1;
    const KS_STATUS_REJECTED    = 2;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesCreditMemo');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_sales_creditmemo';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_sales_creditmemo';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_sales_creditmemo';

    /**
     * Prepare Credit Memo Approval statuses
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
     * @var KsSalesCreditMemoItemFactory
     */
    protected $ksSalesCreditMemoItemFactory;

    /**
     * @var KsSalesOrderItemFactory
     */
    protected $ksSalesOrderItemFactory;

    /**
     * @param ModelContext $ksContext
     * @param Registry $ksRegistry
     * @param KsSalesCreditMemoItemFactory $ksSalesCreditMemoItemFactory
     * @param KsSalesOrderItemFactory $ksSalesOrderItemFactory
     * @param AbstractResource|null $ksResource
     * @param AbstractDb|null $ksResourceCollection
     * @param array $ksData
     */
    public function __construct(
        ModelContext $ksContext,
        Registry $ksRegistry,
        KsSalesCreditMemoItemFactory $ksSalesCreditMemoItemFactory,
        KsSalesOrderItemFactory $ksSalesOrderItemFactory,
        AbstractResource $ksResource = null,
        AbstractDb $ksResourceCollection = null,
        array $ksData = []
    ) {
        $this->ksSalesOrderItemFactory = $ksSalesOrderItemFactory;
        $this->ksSalesCreditMemoItemFactory = $ksSalesCreditMemoItemFactory;
        parent::__construct($ksContext, $ksRegistry, $ksResource, $ksResourceCollection, $ksData);
    }

    /**
     * Set Memo Details
     *
     * @param Object $ksOrder
     * @param Int    $ksSellerId
     */
    public function setKsCreditMemo($ksCreditMemo, $ksSellerId, $ksApprovalReq, $creditMemoData, $creditMemoIncrementId=0)
    {
        $ksData = [
            'ks_store_id'=>$ksCreditMemo->getStoreId(),
            'ks_seller_id'=>$ksSellerId,
            'ks_approval_required' => $ksApprovalReq,
            'ks_approval_status' => $ksApprovalReq==1 ? self::KS_STATUS_PENDING : self::KS_STATUS_APPROVED,
            'ks_adjustment_positive' => $ksCreditMemo->getAdjustmentPositive(),
            'ks_base_discount_amount' => $ksCreditMemo->getBaseDiscountAmount(),
            'ks_grand_total' => $ksCreditMemo->getGrandTotal(),
            'ks_base_adjustment_negative' => $ksCreditMemo->getBaseAdjustmentNegative(),
            'ks_adjustment_negative' => $ksCreditMemo->getAdjustmentNegative(),
            'ks_base_adjustment' => $ksCreditMemo->getBaseAdjustment(),
            'ks_base_subtotal' => $ksCreditMemo->getBaseSubtotal(),
            'ks_discount_amount' => $ksCreditMemo->getDiscountAmount(),
            'ks_subtotal' => $ksCreditMemo->getSubtotal(),
            'ks_adjustment' => $ksCreditMemo->getAdjustment(),
            'ks_base_grand_total' => $ksCreditMemo->getBaseGrandTotal(),
            'ks_base_adjustment_positive' => $ksCreditMemo->getBaseAdjustmentPositive(),
            'ks_base_tax_amount' => $ksCreditMemo->getBaseTaxAmount() - $ksCreditMemo->getBaseShippingTaxAmount(),
            'ks_tax_amount' => $ksCreditMemo->getTaxAmount() - $ksCreditMemo->getShippingTaxAmount(),
            'ks_order_id' => $ksCreditMemo->getOrderId(),
            'ks_creditmemo_status' => $ksCreditMemo->getCreditMemoStatus(),
            'ks_state' => $ksCreditMemo->getState(),
            'ks_shipping_address_id' => $ksCreditMemo->getShippingAddressId(),
            'ks_billing_address_id' => $ksCreditMemo->getBillingAddressId(),
            'ks_invoice_id' => $ksCreditMemo->getInvoiceId(),
            'ks_transaction_id' => $ksCreditMemo->getTransactionId(),
            'ks_discount_description' => $ksCreditMemo->getDiscountDescription(),
            'ks_customer_note' => $ksCreditMemo->getCustomerNote(),
            'ks_order_created_at' => $ksCreditMemo->getOrder()->getCreatedAt(),
            'ks_order_increment_id' => $ksCreditMemo->getOrder()->getIncrementId(),
            'ks_comment_customer_notify' => isset($creditMemoData['comment_customer_notify']),
            'ks_send_email' => isset($creditMemoData['send_email']),
            'ks_total_commission' => $creditMemoData['ks_total_commission'],
            'ks_base_total_commission' => $creditMemoData['ks_base_total_commission'],
            'ks_total_earning' => $creditMemoData['ks_total_earning'],
            'ks_base_total_earning' => $creditMemoData['ks_base_total_earning']
        ];
        if ($creditMemoIncrementId) {
            $ksData['ks_creditmemo_increment_id'] = $creditMemoIncrementId;
        }
        return $this->setKsCreditMemoIncrReqId($this->setData($ksData)->save()->getId(), $ksApprovalReq);
    }

    /**
     * Set Memo Increment Id
     *
     * @param Object $ksOrder
     * @param Int    $ksSellerId
     */
    public function setKsCreditMemoIncrReqId($ksCreditMemoId, $ksApprovalReq)
    {
        $ksCreditMemo = $this->load($ksCreditMemoId);
        $ksReqId = 'REQ'.sprintf("%09d", $ksCreditMemoId);
        if ($ksApprovalReq) {
            $ksCreditMemo->setKsCreditmemoIncrementId($ksReqId);
            $ksCreditMemo->setKsRequestIncrementId($ksReqId);
        } else {
            $ksCreditMemo->setKsRequestIncrementId($ksReqId);
        }
        return $ksCreditMemo->save()->getId();
    }

    /**
     * @return $this|KsSalesCreditMemo
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        if (!$this->_actionValidator->isAllowed($this)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Delete operation is forbidden for current area')
            );
        }

        $ksCreditMemoItemData =$this->ksSalesCreditMemoItemFactory->create()->getCollection()
            ->addFieldToFilter('ks_parent_id',$this->getId());

        if ($this->getKsApprovalStatus() != self::KS_STATUS_APPROVED)
        {
            foreach ($ksCreditMemoItemData as $ksCreditMemoItem)
            {
                $ksCreditMemoQty = $ksCreditMemoItem->getKsQty();
                $ksSalesOrderItem = $this->ksSalesOrderItemFactory->create()
                    ->load($ksCreditMemoItem->getKsOrderItemId(),'ks_sales_order_item_id');
                $ksSalesOrderItem->setKsQtyRefunded($ksSalesOrderItem->getKsQtyRefunded() - $ksCreditMemoQty);
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
