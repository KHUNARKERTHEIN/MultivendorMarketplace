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

namespace Ksolves\MultivendorMarketplace\Block\Order\Shipment\View;

use \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;

/**
 * CommentsHistory block
 */
class CommentsHistory extends \Magento\Framework\View\Element\Template
{
    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $ksSalesData = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $ksAdminHelper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory
     */
    protected $ksCommentFactory;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

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
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @param Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Magento\Sales\Helper\Data $ksSalesData
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory $ksCommentFactory
     * @param KsOrderHelper $ksOrderHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface  $ksStoreManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Sales\Helper\Data $ksSalesData,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        \Magento\Framework\Registry $ksRegistry,
        CollectionFactory $ksCommentFactory,
        KsOrderHelper $ksOrderHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Store\Model\StoreManagerInterface  $ksStoreManager,
        array $data = []
    ) {
        $this->ksSalesData = $ksSalesData;
        $this->ksAdminHelper = $ksAdminHelper;
        $this->ksRegistry  = $ksRegistry;
        $this->ksCommentFactory = $ksCommentFactory;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksStoreManager = $ksStoreManager;  
        parent::__construct($ksContext, $data);
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getKsShipment()
    {
        return $this->ksRegistry->registry('current_shipment_request');
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getKsOrder()
    {
        return $this->ksRegistry->registry('current_order');
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
     * @return bool
     */
    public function canSendCommentEmail()
    {
        return $this->ksSalesData->canSendShipmentCommentEmail(
            $this->getKsOrder()->getStore()->getId()
        );
    }

    /**
     * Get comment collection for the shipment 
     *
     * @return Object
     */
    public function getKsComments(){
        if ($this->getKsShipment()->getKsApprovalStatus()) {
            $ksEntityId = $this->ksOrderHelper->getShipmentId($this->getKsShipment()->getEntityId());
            return $this->ksCommentFactory->create()->addFieldToFilter('parent_id',$ksEntityId)->setOrder('entity_id','DESC');
        } else{
            return $this->getKsShipment();
        }
    }

    /**
     * @return bool
     */
    public function ksCheckNotesAndNotifyShipment()
    {
        return $this->ksOrderHelper->checkNotesAndNotifyShipment() && $this->ksSalesData->canSendShipmentCommentEmail($this->getKsOrder()->getStore()->getId());
    }
}
