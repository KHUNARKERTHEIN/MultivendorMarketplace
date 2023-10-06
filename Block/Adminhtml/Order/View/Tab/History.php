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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\View\Tab;

use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipment\CollectionFactory as KsSalesShipmentCollection;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesCreditMemo\CollectionFactory as KsSalesCreditMemoCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesInvoice\CollectionFactory as KsSalesInvoiceCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoice;

/**
 * Order history tab
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
      protected $_template = 'Ksolves_MultivendorMarketplace::order/view/tab/history.phtml';

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
     * @var KsSalesShipmentTrack
     */
    protected $ksSalesShipmentTrack;

    /**
     * @var KsSalesShipmentCollection
     */
    protected $ksSalesShipmentCollection;

    /**
     * @var KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * @var KsSalesCreditMemoCollectionFactory
     */
    protected $ksSalesCreditMemoCollectionFactory;

    /**
     * @var KsSalesCreditMemo
     */
    protected $ksSalesCreditMemo;

    /**
     * @var KsSalesInvoiceCollectionFactory
     */
    protected $ksSalesInvoiceCollectionFactory;

    /**
     * @var KsSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

   /**
    * @param \Magento\Backend\Block\Template\Context $ksContext
    * @param \Magento\Framework\Registry $ksRegistry
    * @param \Magento\Sales\Helper\Admin $ksAdminHelper
    * @param KsSellerHelper $ksSellerHelper
    * @param KsSalesShipmentTrack $ksSalesShipmentTrack
    * @param KsDataHelper $ksDataHelper
    * @param KsSalesShipmentCollection $ksSalesShipmentCollection
    * @param KsSalesShipment $ksSalesShipment
    * @param KsSalesCreditMemoCollectionFactory $ksSalesCreditMemoCollectionFactory
    * @param KsSalesCreditMemo $ksSalesCreditMemo
    * @param KsSalesInvoiceCollectionFactory $ksSalesInvoiceCollectionFactory
    * @param KsSalesInvoice $ksSalesInvoice
    * @param array $data
    */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        KsSellerHelper $ksSellerHelper,
        KsSalesShipmentTrack $ksSalesShipmentTrack,
        KsDataHelper $ksDataHelper,
        KsSalesShipmentCollection $ksSalesShipmentCollection,
        KsSalesShipment $ksSalesShipment,
        KsSalesCreditMemoCollectionFactory $ksSalesCreditMemoCollectionFactory,
        KsSalesCreditMemo $ksSalesCreditMemo,
        KsSalesInvoiceCollectionFactory $ksSalesInvoiceCollectionFactory,
        KsSalesInvoice $ksSalesInvoice,
        array $data = []
    ) {
        $this->ksCoreRegistry = $ksRegistry;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSalesShipmentTrack = $ksSalesShipmentTrack;
        $this->ksSalesShipmentCollection = $ksSalesShipmentCollection;
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksSalesCreditMemoCollectionFactory = $ksSalesCreditMemoCollectionFactory;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
        $this->ksSalesInvoiceCollectionFactory = $ksSalesInvoiceCollectionFactory;
        $this->ksSalesInvoice = $ksSalesInvoice;
        $this->ksAdminHelper = $ksAdminHelper;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext, $data);
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
     *  get seller name by seller id
     * @param int $ksSellerId
     * @return string
     */
    public function getKsSellerName($ksSellerId)
    {
        if($ksSellerId==0){
            $ksAdminInfo = $this->ksDataHelper->getKsAdminInfo();
            return $ksAdminInfo['name'];
        }
        return $this->ksSellerHelper->getKsSellerName($ksSellerId);
    }

    /**
     * Compose and get order full history.
     *
     * Consists of the status history comments as well as of invoices, shipments and creditmemos creations
     *
     * @TODO This method requires refactoring. Need to create separate model for comment history handling
     * and avoid generating it dynamically
     *
     * @return array
     */
    public function getFullHistory()
    {
        $ksOrder = $this->getOrder();
        $ksHistory = [];
        foreach ($ksOrder->getAllStatusHistory() as $ksOrderComment) {
            $ksHistoryItem = $this->_prepareHistoryItem(
                $ksOrderComment->getStatusLabel(),
                $ksOrderComment->getIsCustomerNotified(),
                $this->getOrderAdminDate($ksOrderComment->getCreatedAt()),
                $ksOrderComment->getComment()
            );
            if(isset($ksHistory[$ksOrderComment->getKsSellerId()])){
                array_push($ksHistory[$ksOrderComment->getKsSellerId()],$ksHistoryItem);
            } else{
                $ksHistory[$ksOrderComment->getKsSellerId()][] = $ksHistoryItem;
            }
        }

        foreach ($ksOrder->getCreditmemosCollection() as $ksMemo) {
            $ksHistoryItem = $this->_prepareHistoryItem(
                __('Credit memo #%1 created', $ksMemo->getIncrementId()),
                $ksMemo->getEmailSent(),
                $this->getOrderAdminDate($ksMemo->getCreatedAt())
            );
            $ksSellerId=0;
            $ksSalesCreditMemo=$this->ksSalesCreditMemo->load($ksMemo->getIncrementId(),'ks_creditmemo_increment_id');

            if($ksSalesCreditMemo->getKsSellerId()){
                $ksSellerId=$ksSalesCreditMemo->getKsSellerId();            
            }else{
                $ksSellerId=0;
            }
            if(isset($ksHistory[$ksSellerId])){
                array_push($ksHistory[$ksSellerId],$ksHistoryItem);
            } else{
                $ksHistory[$ksSellerId][] = $ksHistoryItem;
            }

            foreach ($ksMemo->getCommentsCollection() as $ksComment) {
                $ksHistoryItem = $this->_prepareHistoryItem(
                    __('Credit memo #%1 comment added', $ksMemo->getIncrementId()),
                    $ksComment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($ksComment->getCreatedAt()),
                    $ksComment->getComment()
                );
                if(isset($ksHistory[$ksComment->getKsSellerId()])){
                    array_push($ksHistory[$ksComment->getKsSellerId()],$ksHistoryItem);
                } else{
                    $ksHistory[$ksComment->getKsSellerId()][] = $ksHistoryItem;
                }
            }

        }

        foreach ($ksOrder->getShipmentsCollection() as $ksShipment) {
            $ksHistoryItem = $this->_prepareHistoryItem(
                __('Shipment #%1 created', $ksShipment->getIncrementId()),
                $ksShipment->getEmailSent(),
                $this->getOrderAdminDate($ksShipment->getCreatedAt())
            );
            $ksSellerId=0;
            $ksSalesShipment=$this->ksSalesShipment->load($ksShipment->getIncrementId(),'ks_shipment_increment_id');

            if($ksSalesShipment->getKsSellerId()){
                $ksSellerId=$ksSalesShipment->getKsSellerId();                
            }else{
                $ksSellerId=0;
            }
            if(isset($ksHistory[$ksSellerId])){
                array_push($ksHistory[$ksSellerId],$ksHistoryItem);
            }else{
                $ksHistory[$ksSellerId][] = $ksHistoryItem;
            }

            foreach ($ksShipment->getCommentsCollection() as $ksComment) {
                $ksHistoryItem = $this->_prepareHistoryItem(
                    __('Shipment #%1 comment added', $ksShipment->getIncrementId()),
                    $ksComment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($ksComment->getCreatedAt()),
                    $ksComment->getComment()
                );
                if(isset($ksHistory[$ksComment->getKsSellerId()])){
                    array_push($ksHistory[$ksComment->getKsSellerId()],$ksHistoryItem);
                } else{
                        $ksHistory[$ksComment->getKsSellerId()][] = $ksHistoryItem;
                }
            }
        }

        foreach ($ksOrder->getInvoiceCollection() as $ksInvoice) {
            $ksHistoryItem = $this->_prepareHistoryItem(
                __('Invoice #%1 created', $ksInvoice->getIncrementId()),
                $ksInvoice->getEmailSent(),
                $this->getOrderAdminDate($ksInvoice->getCreatedAt())
            );
            $ksSellerId=0;
            $ksSalesInvoice=$this->ksSalesInvoice->load($ksInvoice->getIncrementId(),'ks_invoice_increment_id');

            if($ksSalesInvoice->getKsSellerId()){
                $ksSellerId=$ksSalesInvoice->getKsSellerId();             
            }else{
                $ksSellerId=0;
            }
            if(isset($ksHistory[$ksSellerId])){
                array_push($ksHistory[$ksSellerId],$ksHistoryItem);
            } else{
                $ksHistory[$ksSellerId][] = $ksHistoryItem;
            }

            foreach ($ksInvoice->getCommentsCollection() as $ksComment) {
                $ksHistoryItem = $this->_prepareHistoryItem(
                    __('Invoice #%1 comment added', $ksInvoice->getIncrementId()),
                    $ksComment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($ksComment->getCreatedAt()),
                    $ksComment->getComment()
                );
                if(isset($ksHistory[$ksComment->getKsSellerId()])){
                    array_push($ksHistory[$ksComment->getKsSellerId()],$ksHistoryItem);
                } else{
                    $ksHistory[$ksComment->getKsSellerId()][] = $ksHistoryItem;
                }
            }
        }

        foreach ($ksOrder->getTracksCollection() as $ksTrack) {
            $ksHistoryItem = $this->_prepareHistoryItem(
                __('Tracking number %1 for %2 assigned', $ksTrack->getTrackNumber(), $ksTrack->getTitle()),
                false,
                $this->getOrderAdminDate($ksTrack->getCreatedAt())
            );
            $ksSellerId=0;
            if($ksTrack->getKsSellerId()){
                $ksSellerId=$ksTrack->getKsSellerId();             
            }else{
                $ksSellerId=0;
            }
            if(isset($ksHistory[$ksSellerId])){
                array_push($ksHistory[$ksSellerId],$ksHistoryItem);
            } else{
                $ksHistory[$ksSellerId][] = $ksHistoryItem;
            }              
        }

        foreach($ksHistory as $ksSellerId => $history){
             usort($history, [__CLASS__, 'sortHistoryByTimestamp']);
        }
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
        return $this->getUrl('multivendor/order/commentsHistory',['_current' => true]);
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
}
