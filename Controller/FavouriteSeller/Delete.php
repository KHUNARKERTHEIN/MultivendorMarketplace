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
use Magento\Framework\Controller\ResultFactory;

/**
 * Delete Controller Class
 */
class Delete extends \Magento\Framework\App\Action\Action
{

    /**
     * @var KsFavouriteSellerFactory
     */
    protected $ksFavouriteSellerFactory;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksFavouriteSellerHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;
    
    /**
     * Constructor
     *
     * @param Magento\Framework\App\Action\Context $ksContext
     * @param KsFavouriteSellerFactory $ksFavouriteSellerFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        KsFavouriteSellerFactory $ksFavouriteSellerFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
    ) {
        $this->ksFavouriteSellerFactory = $ksFavouriteSellerFactory;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }
    
    /**
     * Execute Action for delete
     */
    public function execute()
    {
        if ($this->ksFavouriteSellerHelper->getKsCustomerId() && $this->ksFavouriteSellerHelper->isKsEnabled()) {
            try {
                $ksData = (array)$this->getRequest()->getParams();
                if ($ksData) {
                    $ksModel = $this->ksFavouriteSellerFactory->create()->load($ksData['id']);
                    $ksStoreName = $this->ksFavouriteSellerHelper->getKsStoreName($ksModel->getKsSellerId());
                    $ksModel->delete();
                    $this->ksSellerHelper->ksFlushCache();
                    $this->messageManager->addSuccessMessage(__("\"%1\" has been removed from the favourite sellers listing successfully.", $ksStoreName));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e, __("We can\'t delete record, Please try again."));
            }
            $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $ksResultRedirect;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
