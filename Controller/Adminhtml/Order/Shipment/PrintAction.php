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

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * Class PrintAction
 */
class PrintAction extends \Magento\Backend\App\Action
{
    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ForwardFactory $resultForwardFactory
    ) {
        $this->_fileFactory = $fileFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Forward
     * @throws \Exception
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $shipment = $this->_objectManager->create(\Magento\Sales\Model\Order\Shipment::class)->load($shipmentId);
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

                return $this->_fileFactory->create(
                    'packingslip' . $date . '.pdf',
                    $fileContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } else {
            /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
    }
}
