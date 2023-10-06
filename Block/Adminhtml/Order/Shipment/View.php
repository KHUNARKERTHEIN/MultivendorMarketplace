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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\Shipment;

/**
 * View block
 * 
 */
class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Admin session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry = null;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Sales\Model\Order\Shipment
     */
    private $ksShipment;


    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendSession
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Model\Auth\Session $backendSession,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Model\Order\Shipment $ksShipment,
        array $data = []
    ) {
        $this->_backendSession = $backendSession;
        $this->ksRegistry = $ksRegistry;
        $this->ksShipment = $ksShipment;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _construct()
    {
        $this->_objectId = 'shipment_id';
        $this->_controller = 'adminhtml_order_shipment';
        $this->_mode = 'view';
        $this->_session = $this->_backendSession;

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');

        if (!$this->getShipment()) {
            return;
        }

        
        $shipmentId = $this->getShipmentId(); //Actual Shipment Id
        if ($this->getShipment()->getKsApprovalStatus() == \Ksolves\MultivendorMarketplace\Model\KsSalesShipment::KS_STATUS_APPROVED) {
            $this->buttonList->add(
                'print',
                [
                    'label' => __('Print'),
                    'class' => 'save',
                    'onclick' => 'setLocation(\'' . $this->getPrintUrl() . '\')'
                ]
            );
            

            $this->buttonList->add(
                'send_notification',
                [
                    'label' => __('Send Tracking Information'),
                    'class' => 'send-email',
                    'onclick' => 'confirmSetLocation(\'' . __(
                        'Are you sure you want to send a Shipment email to customer?'
                    ) . '\', \'' . $this->getEmailUrl() . '\')'
                ]
            );
        } else {
            $this->buttonList->add(
                'approve',
                [
                    'label' => __('Approve'),
                    'class' => 'approve',
                    'onclick' => 'setLocation(\'' . $this->getKsApproveUrl() . '\')'
                ]
            );
            if ($this->getShipment()->getKsApprovalStatus() != \Ksolves\MultivendorMarketplace\Model\KsSalesShipment::KS_STATUS_REJECTED) {
                $this->buttonList->add(
                    'reject',
                    [
                        'label' => __('Reject'),
                        'class' => 'ks-reject-view'                ]
                );
            }
        }
            
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
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
        $shipment_increment_id = $this->getShipment()->getKsShipmentIncrementId();
        $shipmentId = $this->ksShipment->loadByIncrementId($shipment_increment_id)->getId();
        return $shipmentId;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->getShipment()->getEmailSent()) {
            $emailSent = __('the shipment email was sent');
        } else {
            $emailSent = __('the shipment email is not sent');
        }
        return __(
            'Shipment #%1 | %3 (%2)',
            $this->getShipment()->getIncrementId(),
            $emailSent,
            $this->formatDate(
                $this->_localeDate->date(new \DateTime($this->getShipment()->getCreatedAt())),
                \IntlDateFormatter::MEDIUM,
                true
            )
        );
    }

    /**
     * Get back url
     *
     * @return string
     */
    public function getBackUrl()
    {
         return sprintf("Javascript:history.back();");
    }


    /**
     * Get email url
     *
     * @return string
     */
    public function getEmailUrl()
    {
        return $this->getUrl(
            'multivendor/*/email',
            ['order_id' => $this->getShipment()->getKsOrderId(), 'ks_shipment_id' => $this->getShipment()->getId(), 'shipment_id' => $this->getShipmentId()]
        );
    }

    /**
     * Get print url
     *
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('multivendor/*/print', ['shipment_id' => $this->getShipmentId()]);
    }

    /**
     * Get approve url
     *
     * @return string
     */
    public function getKsApproveUrl()
    {
        return $this->getUrl('multivendor/order_shipment/approve', ['entity_id' => $this->getShipment()->getId()]);
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getShipment()->getBackUrl()) {
                return $this->buttonList->update(
                    'back',
                    'onclick',
                    'setLocation(\'' . $this->getShipment()->getBackUrl() . '\')'
                );
            }
        }
        return $this;
    }
}
