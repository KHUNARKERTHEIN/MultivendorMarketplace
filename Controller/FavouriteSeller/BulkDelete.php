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
 * BulkDelete Controller Class
 */
class BulkDelete extends \Magento\Framework\App\Action\Action
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
     * @param Context $ksContext,
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
     * Execute action for BulkDelete
     *
     * @return url
     */
    public function execute()
    {
        if ($this->ksFavouriteSellerHelper->getKsCustomerId() && $this->ksFavouriteSellerHelper->isKsEnabled()) {
            if ($this->getRequest()->isPost()) {
                try {
                    $ksData = (array)$this->getRequest()->getParam('masssellerids');
                    foreach ($ksData as $ksId) {
                        $ksModel = $this->ksFavouriteSellerFactory->create()->load($ksId);
                        $ksModel->delete();
                        $this->ksSellerHelper->ksFlushCache();
                    }
                    $this->messageManager->addSuccessMessage(__("Favourite Sellers Removed Successfully."));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e, __("We can\'t delete record, Please try again."));
                }
                $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $ksResultRedirect;
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
