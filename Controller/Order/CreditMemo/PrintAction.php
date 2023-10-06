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

namespace Ksolves\MultivendorMarketplace\Controller\Order\CreditMemo;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;

/**
 * Print Controller
 */
class PrintAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $ksFileFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $ksResultForwardFactory;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $ksCreditmemoRepository;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Framework\App\Response\Http\FileFactory $ksFileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory
     * @param CreditmemoRepositoryInterface $ksCreditmemoRepository
     * @param KsOrderHelper $ksOrderHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\App\Response\Http\FileFactory $ksFileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory,
        CreditmemoRepositoryInterface $ksCreditmemoRepository,
        KsOrderHelper $ksOrderHelper
    ) {
        $this->ksFileFactory = $ksFileFactory;
        $this->ksResultForwardFactory = $ksResultForwardFactory;
        $this->ksCreditmemoRepository = $ksCreditmemoRepository;
        $this->ksOrderHelper = $ksOrderHelper;
        parent::__construct($ksContext);
    }

    /**
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Forward
     * @throws \Exception
     */
    public function execute()
    {
        /** @see \Magento\Sales\Controller\Adminhtml\Order\Invoice */
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            $ksCreditmemoId = $this->ksOrderHelper->getCreditMemoId($creditmemoId);
            $creditmemo = $this->ksCreditmemoRepository->get($ksCreditmemoId);
            if ($creditmemo) {
                $pdf = $this->_objectManager->create(
                    \Magento\Sales\Model\Order\Pdf\Creditmemo::class
                )->getPdf(
                    [$creditmemo]
                );
                $date = $this->_objectManager->get(
                    \Magento\Framework\Stdlib\DateTime\DateTime::class
                )->date('Y-m-d_H-i-s');
                $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

                return $this->ksFileFactory->create(
                    \creditmemo::class . $date . '.pdf',
                    $fileContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } else {
            $resultForward = $this->ksResultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
}
