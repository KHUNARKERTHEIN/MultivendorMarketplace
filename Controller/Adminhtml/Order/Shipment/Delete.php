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
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\Shipment;

use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Delete Shipment Controller
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var KsSalesShipmentFactory
     */
    protected $ksSalesShipmentFactory;

    /**
     * @param Context $ksContext
     * @param KsSalesShipmentFactory $ksSalesShipmentFactory
     */
    public function __construct(
        Context $ksContext,
        KsSalesShipmentFactory $ksSalesShipmentFactory
    ) {
        $this->ksSalesShipmentFactory = $ksSalesShipmentFactory;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksShipmentId = $this->getRequest()->getParam('shipment_id');

        try {
            $this->ksSalesShipmentFactory->create()->load($ksShipmentId)->delete();
            $this->messageManager->addSuccessMessage(__('Shipment request has been deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        //for redirecting url
        return $ksResultRedirect;
    }
}
