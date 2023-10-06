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

namespace Ksolves\MultivendorMarketplace\Controller\Order\Shipment;

use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\App\Action;

/**
 * Class Email
 */
class SendTrackingInfo extends \Magento\Framework\App\Action\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $ksShipmentLoader;

    /**
     * @param Action\Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     */
    public function __construct(
        Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $ksShipmentLoader
    ) {
        $this->ksShipmentLoader = $ksShipmentLoader;
        parent::__construct($context);
    }

    /**
     * Send email with shipment data to customer
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->ksShipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->ksShipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->ksShipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
            $this->ksShipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->ksShipmentLoader->load();
            if ($shipment) {
                $this->_objectManager->create(\Magento\Shipping\Model\ShipmentNotifier::class)
                    ->notify($shipment);
                $shipment->save();
                $this->messageManager->addSuccess(__('You sent the shipment.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Cannot send shipment information.'));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());

    }
}
