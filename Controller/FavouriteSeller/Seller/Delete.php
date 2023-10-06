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

use Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
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
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Constructor
     *
     * @param Context $ksContext
     * @param KsFavouriteSellerFactory $KsFavouriteSellerFactory
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        KsFavouriteSellerFactory $ksFavouriteSellerFactory,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksFavouriteSellerFactory = $ksFavouriteSellerFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action for delete
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                $ksId = $this->getRequest()->getParam('id');
                //check data
                if ($ksId) {
                    //get model data
                    $ksModel = $this->ksFavouriteSellerFactory->create()->load($ksId);
                    if ($ksModel) {
                        $ksModel->delete();
                        $this->messageManager->addSuccessMessage(
                            __("A follower has been deleted successfully.")
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __('There is not such Followers available to delete.')
                        );
                    }
                } else {
                    $this->messageManager->addErrorMessage(
                        __('Something went wrong while deleting Followers')
                    );
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
            }
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            //for redirecting url
            return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
