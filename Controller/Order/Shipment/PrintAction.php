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

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;

/**
 * Print Controller
 */
class PrintAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var FileFactory
     */
    protected $ksFileFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var ForwardFactory
     */
    protected $ksResultForwardFactory;

    /**
     * @param Context $ksContext
     * @param FileFactory $ksFileFactory
     * @param ForwardFactory $ksResultForwardFactory
     * @param KsOrderHelper $ksOrderHelper
     */
    public function __construct(
        Context $ksContext,
        FileFactory $ksFileFactory,
        ForwardFactory $ksResultForwardFactory,
        KsOrderHelper $ksOrderHelper
    ) {
        $this->ksFileFactory = $ksFileFactory;
        $this->ksResultForwardFactory = $ksResultForwardFactory;
        $this->ksOrderHelper = $ksOrderHelper;
        parent::__construct($ksContext);
    }

    /**
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Forward
     * @throws \Exception
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $ksShipmentId = $this->ksOrderHelper->getShipmentId($shipmentId);
            $shipment = $this->_objectManager->create(\Magento\Sales\Model\Order\Shipment::class)->load($ksShipmentId);
            if ($shipment) {
                $pdf = $this->_objectManager->create(
                    \Magento\Sales\Model\Order\Pdf\Shipment::class
                )->getPdf(
                    [$shipment]
                );
                $date = $this->_objectManager->get(
                    \Magento\Framework\Stdlib\DateTime\DateTime::class
                )->date('Y-m-d_H-i-s');
                $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

                return $this->ksFileFactory->create(
                    'packingslip' . $date . '.pdf',
                    $fileContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } else {
            /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
            $resultForward = $this->ksResultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
    }
}
