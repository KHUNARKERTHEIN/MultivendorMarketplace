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

namespace Ksolves\MultivendorMarketplace\Controller\FavouriteSeller;

use Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerFactory;
use Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper;

/**
 * NewProductAlert Controller Class
 */
class NewProductAlert extends \Magento\Framework\App\Action\Action
{
    /**
     * @var KsFavouriteSellerHelper
     */
    protected $ksFavouriteSellerHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var KsFavouriteSellerFactory
     */
    protected $ksFavouriteSellerFactory;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param KsFavouriteSellerFactory $ksFavouriteSellerFactory
     * @param \Magento\Framework\Message\ManagerInterface $ksMessageManager
     * @param KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        KsFavouriteSellerFactory $ksFavouriteSellerFactory,
        \Magento\Framework\Message\ManagerInterface $ksMessageManager,
        KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
    ) {
        $this->ksFavouriteSellerFactory = $ksFavouriteSellerFactory;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * New product alert save/remove page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->ksFavouriteSellerHelper->getKsCustomerId()) {
            try {
                $ksId =$this->getRequest()->getPost("id");
                $ksAction =$this->getRequest()->getPost("action");
                $ksModel = $this->ksFavouriteSellerFactory->create()->load($ksId);
                if ($ksAction == "remove") {
                    $ksStatus = 0;
                } else {
                    $ksStatus = 1;
                }
                if ($ksId) {
                    $ksModel->setKsCustomerNewProductAlert($ksStatus);
                    $ksModel->save();
                    $this->ksSellerHelper->ksFlushCache();
                    $ksStoreName = $this->ksFavouriteSellerHelper->getKsStoreName($ksModel->getKsSellerId());
                    if ($ksAction == "remove") {
                        $this->ksMessageManager->addSuccess(__("\"%1\" store new product alert has been disabled successfully.", $ksStoreName));
                    } else {
                        $this->ksMessageManager->addSuccess(__("\"%1\" store new product alert has been enabled successfully.", $ksStoreName));
                    }
                }
            } catch (\Exception $e) {
                $this->ksMessageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
