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

namespace Ksolves\MultivendorMarketplace\Controller\FavouriteSeller\Seller;

use Magento\Framework\App\Action\Context;
use Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerFactory;
use Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper;

/**
 * Controller class for Price Change Alert Status
 */
class PriceChangeAlert extends \Magento\Framework\App\Action\Action
{
    /**
     * @var KsFavouriteSellerFactory
     */
    protected $ksFavouriteSellerFactory;

    /**
     * @var KsFavouriteSellerHelper
     */
    protected $ksFavouriteSellerHelper;

    /**
     * Initialize dependencies
     *
     * @param Context $ksContext
     * @param KsFavouriteSellerFactory $ksFavouriteSellerFactory
     * @param KsFavouriteSellerHelper $ksFavouriteSellerHelper
     */
    public function __construct(
        Context $ksContext,
        KsFavouriteSellerFactory $ksFavouriteSellerFactory,
        KsFavouriteSellerHelper $ksFavouriteSellerHelper
    ) {
        $this->ksFavouriteSellerFactory = $ksFavouriteSellerFactory;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Price change alert enable/disable
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        if ($this->ksFavouriteSellerHelper->getKsCustomerId()) {
            $ksId =$this->getRequest()->getPost("id");
            $ksAction =$this->getRequest()->getPost("action");
            $ksModel = $this->ksFavouriteSellerFactory->create()->load($ksId);
            if ($ksAction == "disable") {
                $ksPriceAlertStatus = \Ksolves\MultivendorMarketplace\Model\KsFavouriteSeller::KS_STATUS_PRICE_CHANGE_DISABLED;
            } else {
                $ksPriceAlertStatus = \Ksolves\MultivendorMarketplace\Model\KsFavouriteSeller::KS_STATUS_PRICE_CHANGE_ENABLED;
            }
            if ($ksId) {
                try {
                    $ksModel->setKsSellerPriceAlert($ksPriceAlertStatus);
                    $ksModel->save();
                    
                    if ($ksAction == "disable") {
                        $ksMsg = "A follower's price change alert has been disabled successfully.";
                    } else {
                        $ksMsg = "A follower's price change alert has been enabled successfully.";
                    }
                    $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['success' => true,
                        'message' => $this->messageManager->addSuccess(__($ksMsg))
                    ]);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $ksResponse = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData(['error' => true,
                            'message' => $this->messageManager->addErrorMessage(__($this->ksMessage($ksAction)))
                        ]);
                } catch (\Exception $e) {
                    $ksResponse = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData(['error' => true,
                            'message' => $this->messageManager->addErrorMessage(__($this->ksMessage($ksAction)))
                        ]);
                }
            } else {
                $ksResponse = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData(['error' => true,
                            'message' => $this->messageManager->addErrorMessage(__($this->ksMessage($ksAction)))
                        ]);
            }
            return $ksResponse;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'multivendor/login/index/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Get error message
     *
     * @param [string] $ksAction
     * @return [string]
     */
    public function ksMessage($ksAction)
    {
        if ($ksAction == "disable") {
            $ksMsg = "Something went wrong while disabling new product alert status.";
        } else {
            $ksMsg = "Something went wrong while enabling new product alert status.";
        }
        return $ksMsg;
    }
}
