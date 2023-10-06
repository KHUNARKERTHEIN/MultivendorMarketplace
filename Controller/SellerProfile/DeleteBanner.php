<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited  (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Controller\SellerProfile;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Ksolves\MultivendorMarketplace\Model\KsBannersFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\Controller\ResultFactory;

/**
 * DeleteBanner Controller class
 */
class DeleteBanner extends \Magento\Framework\App\Action\Action
{

   
    /**
     * @var KsBannersFactory
     */
    protected $ksBannersFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;
    
    /**
     * @param Context $ksContext
     * @param KsBannersFactory $ksBannersFactory
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        Context $ksContext,
        KsBannersFactory $ksBannersFactory,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksBannersFactory = $ksBannersFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Save banner information
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            //get param data
            $ksId = $this->getRequest()->getParam('id');
            $ksCollection = $this->ksBannersFactory->create()->load($ksId);
            //delete collection
            $ksCollection->delete();
            /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
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
