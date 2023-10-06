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
 * KsSalesShipment Model Class
 */
class KsSalesShipment extends AbstractModel
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
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipment');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_sales_shipment';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_sales_shipment';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_sales_shipment';

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
     * @var KsSalesShipmentItemFactory
     */
    protected $ksSalesShipmentItemFactory;

    /**
     * @var KsSalesOrderItemFactory
     */
    protected $ksSalesOrderItemFactory;

    /**
     * @param ModelContext $ksContext
     * @param Registry $ksRegistry
     * @param KsSalesShipmentItemFactory $ksSalesShipmentItemFactory
     * @param KsSalesOrderItemFactory $ksSalesOrderItemFactory
     * @param AbstractResource|null $ksResource
     * @param AbstractDb|null $ksResourceCollection
     * @param array $ksData
     */
    public function __construct(
        ModelContext               $ksContext,
        Registry                   $ksRegistry,
        KsSalesShipmentItemFactory $ksSalesShipmentItemFactory,
        KsSalesOrderItemFactory    $ksSalesOrderItemFactory,
        AbstractResource           $ksResource = null,
        AbstractDb                 $ksResourceCollection = null,
        array                      $ksData = []
    ) {
        $this->ksSalesOrderItemFactory = $ksSalesOrderItemFactory;
        $this->ksSalesShipmentItemFactory = $ksSalesShipmentItemFactory;
        parent::__construct($ksContext, $ksRegistry, $ksResource, $ksResourceCollection, $ksData);
    }

    /**
     * Set Invoice Details
     *
     * @param Object $ksShipment
     * @param Int    $ksSellerId
     */
    public function setKsShipment($ksShipment,$ksSellerId,$ksApprovalReq,$shipmentData,$shipmentIncrementId=0)
    {
        $ksData = [
            'ks_store_id' => $ksShipment->getStoreId(),
            'ks_seller_id' => $ksSellerId,
            'ks_approval_required' => $ksApprovalReq,
            'ks_approval_status' => $ksApprovalReq==1 ? self::KS_STATUS_PENDING : self::KS_STATUS_APPROVED,
            'ks_total_weight' => $ksShipment->getTotalWeight(),
            'ks_total_qty' => $ksShipment->getTotalQty(),
            'ks_order_id' => $ksShipment->getOrderId(),
            'ks_customer_id' => $ksShipment->getCustomerId(),
            'ks_shipping_address_id' => $ksShipment->getShippingAddressId(),
            'ks_billing_address_id' => $ksShipment->getBillingAddressId(),
            'ks_shipment_status' => $ksShipment->getShipmentStatus(),
            'ks_order_created_at' => $ksShipment->getOrder()->getCreatedAt(),
            'ks_order_increment_id' => $ksShipment->getOrder()->getIncrementId(),
            'ks_created_at' => $ksShipment->getCreatedAt(),
            'ks_updated_at' => $ksShipment->getUpdatedAt(),
            'ks_shipping_label' => $ksShipment->getShipmentLabel(),
            'ks_customer_note' => $ksShipment->getCustomerNote(),
            'ks_comment_customer_notify' => isset($shipmentData['comment_customer_notify']),
            'ks_send_email' => isset($shipmentData['send_email'])
        ];
        if ($shipmentIncrementId) {
            $ksData['ks_shipment_increment_id'] = $shipmentIncrementId;
        }
        return $this->setKsShipmentIncrReqId($this->setData($ksData)->save()->getId(),$ksApprovalReq);
    }

    /**
     * Set Invoice Increment Id
     *
     * @param Object $ksOrder
     * @param Int    $ksSellerId
     */
    public function setKsShipmentIncrReqId($ksShipmentId,$ksApprovalReq)
    {
        $ksShipment = $this->load($ksShipmentId);
        $ksReqId = 'REQ'.sprintf("%09d", $ksShipmentId);
        if ($ksApprovalReq) {
            $ksShipment->setKsShipmentIncrementId($ksReqId);
            $ksShipment->setKsRequestIncrementId($ksReqId);
        } else{
            $ksShipment->setKsRequestIncrementId($ksReqId);
        }
        return $ksShipment->save()->getId();
    }

    /**
     * @return $this|KsSalesShipment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        if (!$this->_actionValidator->isAllowed($this)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Delete operation is forbidden for current area')
            );
        }

        $ksShipmentItemData =$this->ksSalesShipmentItemFactory->create()->getCollection()
            ->addFieldToFilter('ks_parent_id',$this->getId());

        if ($this->getKsApprovalStatus() != self::KS_STATUS_APPROVED)
        {
            foreach ($ksShipmentItemData as $ksShipmentItem)
            {
                $ksShipmentQty = $ksShipmentItem->getKsQty();
                $ksSalesOrderItem = $this->ksSalesOrderItemFactory->create()
                    ->load($ksShipmentItem->getKsOrderItemId(),'ks_sales_order_item_id');
                $ksSalesOrderItem->setKsQtyShipped($ksSalesOrderItem->getKsQtyShipped() - $ksShipmentQty);
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
