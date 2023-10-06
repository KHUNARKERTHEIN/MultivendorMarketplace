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

use Magento\Framework\App\ObjectManager;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * KsForm block
 */
class KsForm extends \Ksolves\MultivendorMarketplace\Block\Order\KsAbstractOrder
{
    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

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
     * @var KsDataHelper $ksDataHelper
     */
    protected $ksDataHelper;
    /**
     * @var \Magento\Sales\Model\Order\Shipment
     */
    private $shipment;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Magento\Sales\Model\OrderRepository $ksOrderRepository
     * @param array $data
     * @param ShippingHelper|null $shippingHelper
     * @param TaxHelper|null $taxHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Sales\Model\OrderRepository $ksOrderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        KsDataHelper $ksDataHelper,
        \Magento\Sales\Model\Order\Shipment $shipment,
        \Magento\Store\Model\StoreManagerInterface  $ksStoreManager,
        array $data = [],
        ?ShippingHelper $shippingHelper = null,
        ?TaxHelper $taxHelper = null,
    ) {
        $data['taxHelper'] = $taxHelper ?? ObjectManager::getInstance()->get(TaxHelper::class);
        $this->_carrierFactory = $carrierFactory;
        $data['shippingHelper'] = $shippingHelper ?? ObjectManager::getInstance()->get(ShippingHelper::class);
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksDataHelper = $ksDataHelper;
        $this->shipment = $shipment;
        parent::__construct($ksContext, $ksRegistry, $ksAdminHelper, $ksOrderRepository, $data, $shippingHelper, $taxHelper);
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
     * Retrieve actual shipment id
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipmentId()
    {
        $shipment_increment_id = $this->getKsShipment()->getKsShipmentIncrementId();
        $shipmentId = $this->shipment->loadByIncrementId($shipment_increment_id)->getId();
        return $shipmentId;
    }

    /**
     * @param object $order
     * @return string
     */
    public function getSendShipmentinformation($shipment)
    {
        return $this->getUrl('multivendor/order_shipment/sendtrackinginfo', ['shipment_id' => $this->getShipmentId()]);
    }

    /**
     * Check Product Image Allowed
     * @return bool
     */
    public function checkShipmenttrackinformation()
    {
        return $this->ksDataHelper->getKsConfigShipmentSetting('ks_send_shipment_tracking_information', 0);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getShipmentUrl()
    {
       return sprintf("Javascript:history.back();");
    }
  
    /**
     * Get print url
     *
     * @return string
     */
    public function getKsPrintUrl()
    {
        return $this->getUrl('multivendor/*/print', ['shipment_id' => $this->getKsShipment()->getId()]);
    }

    /**
     * @return string
     */
    public function getKsReSubmitUrl()
    {
        return $this->getUrl('multivendor/order_shipment/resubmit');
    } 
}
