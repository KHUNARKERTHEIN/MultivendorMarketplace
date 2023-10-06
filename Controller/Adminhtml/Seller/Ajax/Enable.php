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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller\Ajax;

use Magento\Backend\App\Action\Context;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Enable Controller class for Seller Store Status
 */
class Enable extends \Magento\Backend\App\Action
{
    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var DateTime
     */
    protected $ksDate;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param KsSellerFactory $ksSellerFactory
     * @param DateTime $KsDate
     */
    public function __construct(
        Context $ksContext,
        KsSellerFactory $ksSellerFactory,
        DateTime $ksDate
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksDate = $ksDate;
        parent::__construct($ksContext);
    }

    /**
     * Seller reject
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        $ksId =$this->getRequest()->getPost("id");

        $ksSellerStoreStatus = \Ksolves\MultivendorMarketplace\Model\KsSellerStore::KS_STATUS_ENABLED;
        if ($ksId) {
            try {
                $ksModel = $this->ksSellerFactory->create()->load($ksId);
                if ($ksModel->getKsSellerStatus() == 1) {
                    $ksModel->setKsStoreStatus($ksSellerStoreStatus);
                    $ksModel->setKsUpdatedAt($this->ksDate->gmtDate());
                    $ksModel->save();

                    $this->_eventManager->dispatch('ksseller_store_change_after');

                    $ksResponse = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData(['success' => true,
                        'message' => $this->messageManager->addSuccess("A seller's store has been enabled successfully.")
                    ]);
                } elseif ($ksModel->getKsSellerStatus() == 0) {
                    $ksResponse = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData(['success' => true,
                        'message' => $this->messageManager->addErrorMessage("A store of pending seller can't be enabled.")
                    ]);
                } else {
                    $ksResponse = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData(['success' => true,
                        'message' => $this->messageManager->addErrorMessage("A store of rejected seller can't be enabled.")
                    ]);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage("Something went wrong while enabling seller's store.")
                    ]);
            } catch (\Exception $e) {
                $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage("Something went wrong while enabling seller's store.")
                    ]);
            }
        } else {
            $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage("Something went wrong while enabling seller's store.")
                    ]);
        }
        return $ksResponse;
    }
}
