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
namespace Ksolves\MultivendorMarketplace\Controller\SellerLocator;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * SellerLocator Save Controller class
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerLocatorFactory
     */
    protected $ksSellerLocatorFactory;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;
 
    /**
     * SellerLocator Save Constructor
     *
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerLoctorFactory $ksSellerLocatorFactory
     * @param \Magento\Framework\Message\ManagerInterface $ksMessageManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Model\KsSellerLocatorFactory $ksSellerLocatorFactory,
        \Magento\Framework\Message\ManagerInterface $ksMessageManager
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSellerLocatorFactory = $ksSellerLocatorFactory;
        $this->ksMessageManager = $ksMessageManager;
        parent::__construct($ksContext);
    }

    /**
     * Save location details of seller
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller) {
            try {
                //Get data
                $ksModel = $this->ksSellerLocatorFactory->create();
                $ksPostData = $this->getRequest()->getPostValue();
                if (isset($ksPostData['id'])) {
                    $ksModel->load($ksPostData['id']);
                }
                $ksModel->setKsLocation($ksPostData['ks_location']);
                $ksModel->setKsLatitude($ksPostData['ks_latitude']);
                $ksModel->setKsLongitude($ksPostData['ks_longitude']);
                $ksModel->setKsSellerId($ksPostData['ks_seller_id']);
                $ksModel->save();
                $this->ksMessageManager->addSuccess(__('The seller location details has been saved successfully.'));
            } catch (\Exception $e) {
                $this->ksMessageManager->addError(__('An error occured while saving your data.'));
            }
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            //for redirecting url
            return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login', ['_secure' => $this->getRequest()->isSecure()]);
        }
    }
}
