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

namespace Ksolves\MultivendorMarketplace\Block\Order\View\Tab;

use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesCreditMemo\CollectionFactory as KsSalesCreditMemoCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as CreditMemoCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsShipmentListing\CollectionFactory as KsSalesShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesInvoice\CollectionFactory as KsSalesInvoiceCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoice;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipmentTrack\CollectionFactory as KsSalesShipmentTrackCollectionFactory;

/**
 * Order history tab
 *
 */
class History extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $ksAdminHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsSalesCreditMemo
     */
    protected $ksSalesCreditMemo;

    /**
     * @var KsSalesShipmentTrack
     */
    protected $ksSalesShipmentTrack;

    /**
     * @var KsSalesCreditMemoCollectionFactory
     */
    protected $ksSalesMemoCollectionFactory;

    /**
     * @var CreditMemoCollectionFactory
     */
    protected $ksCreditMemoCollection;
    /**
     * @var ShipmentCollectionFactory 
     */
    protected $ksShipmentCollection;

    /**
     * @var KsSalesShipmentCollectionFactory
     */
    protected $ksSalesShipmentCollectionFactory;

    /**
     * @var KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * @var InvoiceCollectionFactory
     */
    protected $ksInvoiceCollection;

    /**
     * @var KsSalesInvoiceCollectionFactory
     */
    protected $ksSalesInvoiceCollectionFactory;

    /**
     * @var KsSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * @var KsSalesShipmentTrackCollectionFactory
     */
    protected $ksSalesShipmentTrackCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param CreditMemoCollectionFactory $ksCreditMemoCollection
     * @param array $data
     * @param KsSellerHelper $ksSellerHelper
     * @param KsSalesCreditMemoCollectionFactory $ksSalesMemoCollectionFactory
     * @param KsSalesCreditMemo $ksSalesCreditMemo
     * @param ShipmentCollectionFactory $ksShipmentCollection
     * @param KsSalesShipmentCollectionFactory $ksSalesShipmentCollectionFactory
     * @param KsSalesShipment $ksSalesShipment
     * @param KsSalesShipmentTrack $ksSalesShipmentTrack
     * @param KsSalesShipmentTrackCollectionFactory $ksSalesShipmentTrackCollectionFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        KsSellerHelper $ksSellerHelper,
        CreditMemoCollectionFactory $ksCreditMemoCollection,
        KsSalesCreditMemoCollectionFactory $ksSalesMemoCollectionFactory,
        KsSalesCreditMemo $ksSalesCreditMemo,
        ShipmentCollectionFactory $ksShipmentCollection,
        KsSalesShipmentCollectionFactory $ksSalesShipmentCollectionFactory,
        KsSalesShipment $ksSalesShipment,
        InvoiceCollectionFactory $ksInvoiceCollection,
        KsSalesInvoiceCollectionFactory $ksSalesInvoiceCollectionFactory,
        KsSalesInvoice $ksSalesInvoice,
        KsSalesShipmentTrack $ksSalesShipmentTrack,
        KsSalesShipmentTrackCollectionFactory $ksSalesShipmentTrackCollectionFactory,
        array $data = []
    ) {
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCreditMemoCollection = $ksCreditMemoCollection;
        $this->ksSalesMemoCollectionFactory = $ksSalesMemoCollectionFactory;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
        $this->ksShipmentCollection = $ksShipmentCollection;
        $this->ksSalesShipmentCollectionFactory = $ksSalesShipmentCollectionFactory;
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksInvoiceCollection = $ksInvoiceCollection;
        $this->ksSalesInvoiceCollectionFactory = $ksSalesInvoiceCollectionFactory;
        $this->ksSalesInvoice = $ksSalesInvoice;
        $this->ksSalesShipmentTrack = $ksSalesShipmentTrack;
        $this->ksSalesShipmentTrackCollectionFactory = $ksSalesShipmentTrackCollectionFactory;
        parent::__construct($ksContext, $data);
        $this->ksAdminHelper = $ksAdminHelper;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->ksCoreRegistry->registry('current_order');
    }

    /**
     * Compose and get order full history.
     *
     * Consists of the status history comments as well as of invoices, shipments and creditmemos creations
     *
     * @return array
     */
    public function getFullHistory()
    {
        $ksOrder = $this->getOrder();
        $ksSellerId =$this->ksSellerHelper->getKsCustomerId();
        $ksHistory = [];
        foreach ($ksOrder->getAllStatusHistory() as $ksOrderComment) {
            if($ksOrderComment->getKsSellerId()==$ksSellerId || $ksOrderComment->getKsSellerId()==0){
                $ksHistory[] = $this->_prepareHistoryItem(
                    $ksOrderComment->getStatusLabel(),
                    $ksOrderComment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($ksOrderComment->getCreatedAt()),
                    $ksOrderComment->getComment()
                );
            }
        }

        foreach ($this->getKsCreditmemosCollection() as $ksMemo) {            
            $ksHistory[] = $this->_prepareHistoryItem(
                __('Credit memo #%1 created', $ksMemo->getIncrementId()),
                $ksMemo->getEmailSent(),
                $this->getOrderAdminDate($ksMemo->getCreatedAt())
            );
    

            foreach ($ksMemo->getCommentsCollection() as $ksComment) {                
                $ksHistory[] = $this->_prepareHistoryItem(
                    __('Credit memo #%1 comment added', $ksMemo->getIncrementId()),
                    $ksComment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($ksComment->getCreatedAt()),
                    $ksComment->getComment()
                );
            }
        }

        foreach ($this->getKsShipmentCollection() as $ksShipment) {
            $ksHistory[] = $this->_prepareHistoryItem(
                __('Shipment #%1 created', $ksShipment->getIncrementId()),
                $ksShipment->getEmailSent(),
                $this->getOrderAdminDate($ksShipment->getCreatedAt())
            );

            foreach ($ksShipment->getCommentsCollection() as $ksComment) {                
                    $ksHistory[] = $this->_prepareHistoryItem(
                        __('Shipment #%1 comment added', $ksShipment->getIncrementId()),
                        $ksComment->getIsCustomerNotified(),
                        $this->getOrderAdminDate($ksComment->getCreatedAt()),
                        $ksComment->getComment()
                    );
                }
            }

        foreach ($this->getKsInvoiceCollection() as $ksInvoice) {
           
                $ksHistory[] = $this->_prepareHistoryItem(
                    __('Invoice #%1 created', $ksInvoice->getIncrementId()),
                    $ksInvoice->getEmailSent(),
                    $this->getOrderAdminDate($ksInvoice->getCreatedAt())
            );

            foreach ($ksInvoice->getCommentsCollection() as $ksComment) {
                $ksHistory[] = $this->_prepareHistoryItem(
                    __('Invoice #%1 comment added', $ksInvoice->getIncrementId()),
                    $ksComment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($ksComment->getCreatedAt()),
                    $ksComment->getComment()
                );
            }
        }

        foreach ($this->getKsTrackingDetails() as $ksTrack) {
            $ksHistory[] = $this->_prepareHistoryItem(
                __('Tracking number %1 for %2 assigned', $ksTrack->getKsTrackNumber(), $ksTrack->getKsTitle()),
                false,
                $this->getOrderAdminDate($ksTrack->getKsCreatedAt())
            );
        }

        usort($ksHistory, [__CLASS__, 'sortHistoryByTimestamp']);
        return $ksHistory;
    }

    /**
     * Status history date/datetime getter
     *
     * @param array $item
     * @param string $dateType
     * @param int $format
     * @return string
     */
    public function getItemCreatedAt(array $item, $dateType = 'date', $format = \IntlDateFormatter::MEDIUM)
    {
        if (!isset($item['created_at'])) {
            return '';
        }
        if ('date' === $dateType) {
            return $this->formatDate($item['created_at'], $format);
        }
        return $this->formatTime($item['created_at'], $format);
    }

    /**
     * Status history item title getter
     *
     * @param array $item
     * @return string
     */
    public function getItemTitle(array $item)
    {
        return isset($item['title']) ? $this->escapeHtml($item['title']) : '';
    }

    /**
     * Check whether status history comment is with customer notification
     *
     * @param array $item
     * @param bool $isSimpleCheck
     * @return bool
     */
    public function isItemNotified(array $item, $isSimpleCheck = true)
    {
        if ($isSimpleCheck) {
            return !empty($item['notified']);
        }
        return isset($item['notified']) && false !== $item['notified'];
    }

    /**
     * Status history item comment getter
     *
     * @param array $item
     * @return string
     */
    public function getItemComment(array $item)
    {
        $allowedTags = ['b', 'br', 'strong', 'i', 'u', 'a'];
        return isset($item['comment'])
            ? $this->ksAdminHelper->escapeHtmlWithLinks($item['comment'], $allowedTags) : '';
    }

    /**
     * Map history items as array
     *
     * @param string $label
     * @param bool $notified
     * @param \DateTimeInterface $created
     * @param string $comment
     * @return array
     */
    protected function _prepareHistoryItem($label, $notified, $created, $comment = '')
    {
        return ['title' => $label, 'notified' => $notified, 'comment' => $comment, 'created_at' => $created];
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Comments History');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Order History');
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('multivendor/order/commentshistory',['_current' => true]);
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Customer Notification Applicable check method
     *
     * @param array $historyItem
     * @return bool
     */
    public function isCustomerNotificationNotApplicable($historyItem)
    {
        return $historyItem['notified'] ==
            \Magento\Sales\Model\Order\Status\History::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
    }

    /**
     * Comparison For Sorting History By Timestamp
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    public static function sortHistoryByTimestamp($a, $b)
    {
        $createdAtA = $a['created_at'];
        $createdAtB = $b['created_at'];

        return $createdAtA->getTimestamp() <=> $createdAtB->getTimestamp();
    }

    /**
     * Get order admin date
     *
     * @param int $createdAt
     * @return \DateTime
     */
    public function getOrderAdminDate($createdAt)
    {
        return $this->_localeDate->date(new \DateTime($createdAt));
    }

    /**
     * Retrieve order creditmemos collection
     *
     * @return CreditmemoCollection|false
     */
    public function getKsCreditmemosCollection()
    {

        $collection = $this->ksSalesMemoCollectionFactory->create()->addFieldToFilter('ks_seller_id',$this->ksSellerHelper->getKsCustomerId())->addFieldToFilter('ks_order_id',$this->getOrder()->getId())->addFieldToFilter('ks_approval_status',$this->ksSalesCreditMemo::KS_STATUS_APPROVED)->addFieldToSelect('ks_creditmemo_increment_id')->getData();
        $ksIncIds =[];
        foreach($collection as $incId){
            array_push($ksIncIds,$incId['ks_creditmemo_increment_id']);
        }    

        $creditmemoCollection = $this->ksCreditMemoCollection->create()->addFieldToFilter('increment_id',['in' => $ksIncIds]);
        return $creditmemoCollection;
    }

    /**
     * Retrieve order shipments collection
     *
     * @return ShipmentCollection|false
     */
    public function getKsShipmentCollection()
    {
        $collection = $this->ksSalesShipmentCollectionFactory->create()->addFieldToFilter('ks_seller_id',$this->ksSellerHelper->getKsCustomerId())->addFieldToFilter('ks_order_id',$this->getOrder()->getId())->addFieldToFilter('ks_approval_status',$this->ksSalesShipment::KS_STATUS_APPROVED)->addFieldToSelect('ks_shipment_increment_id')->getData();
        $ksIncIds =[];
        foreach($collection as $incId){
            array_push($ksIncIds,$incId['ks_shipment_increment_id']);
        }   

        $shipmentCollection = $this->ksShipmentCollection->create()->addFieldToFilter('increment_id',['in' => $ksIncIds]);
        return $shipmentCollection;
    }

    /**
     * Retrieve order invoice collection
     *
     * @return InvoiceCollection|false
     */
    public function getKsInvoiceCollection()
    {
        $collection = $this->ksSalesInvoiceCollectionFactory->create()->addFieldToFilter('ks_seller_id',$this->ksSellerHelper->getKsCustomerId())->addFieldToFilter('ks_order_id',$this->getOrder()->getId())->addFieldToFilter('ks_approval_status',$this->ksSalesInvoice::KS_STATUS_APPROVED)->addFieldToSelect('ks_invoice_increment_id')->getData();
        $ksIncIds =[];
        foreach($collection as $incId){
            array_push($ksIncIds,$incId['ks_invoice_increment_id']);
        }   

        $invoiceCollection = $this->ksInvoiceCollection->create()->addFieldToFilter('increment_id',['in' => $ksIncIds]);
        return $invoiceCollection;
    }

    /**
     * Get Tracking items
     *
     * @param Int    $ksOrderId
     * @param Int    $ksSellerId
     */
    public function getKsTrackingDetails()
    {
        $collection = $this->ksSalesShipmentCollectionFactory->create()->addFieldToFilter('ks_seller_id',$this->ksSellerHelper->getKsCustomerId())->addFieldToFilter('ks_order_id',$this->getOrder()->getId())->addFieldToFilter('ks_approval_status',$this->ksSalesShipment::KS_STATUS_APPROVED)->addFieldToSelect('entity_id')->getData();
        $ksIncIds =[];
        foreach($collection as $incId){
            array_push($ksIncIds,$incId['entity_id']);
        }   

        $shipmentCollection = $this->ksSalesShipmentTrackCollectionFactory->create()->addFieldToFilter('ks_parent_id',['in' => $ksIncIds]);
       
        return $shipmentCollection;
        
    }

    /**
     *  get seller name by seller id
     * @param int $ksSellerId
     * @return string
     */
    public function getKsSellerName($ksSellerId)
    {
        $ksSellerId =$this->ksSellerHelper->getKsCustomerId();
        if($ksSellerId==0){
            return "Admin";
        }
        return $this->ksSellerHelper->getKsSellerName($ksSellerId);
       
    }
}
