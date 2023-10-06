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
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller;

/**
 * Delete Controller class
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory
     */
    protected $ksSellerStoreFactory;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Initialize constructor.
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory $ksSellerStoreFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory $ksSellerStoreFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksSellerStoreFactory = $ksSellerStoreFactory;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action
     */
    public function execute()
    {
        $ksData = $this->getRequest()->getParams();

        // get the url to redirect to seller list or pending approval seller list
        if (str_contains($this->_redirect->getRefererUrl(), 'multivendor/seller/pendingedit')) {
            $ksRedirectUrl = '*/*/sellerpendinglist';
        } else {
            $ksRedirectUrl = '*/*/';
        }

        //check data
        if ($ksData) {
            //get seller store model data
            $ksSellerModel = $this->ksSellerFactory->create()->load($ksData['id'], 'ks_seller_id');
            $ksEmail = $this->ksSellerHelper->getKsCustomerEmail($ksData['id']);

            //check model data
            if ($ksSellerModel) {
                try {
                    // seller store collection
                    $ksSellerStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['id']);
                    if ($ksSellerStoreCollection->getSize() > 0) {
                        foreach ($ksSellerStoreCollection as $ksValue) {
                            $ksSellerStoreModel = $this->ksSellerStoreFactory->create()->load($ksValue->getId());
                            $ksSellerStoreModel->delete();
                        }
                    }
                    $this->ksProductHelper->ksChangeProductStatus($ksData['id'], $ksEmail);
                    //deleting data
                    $ksSellerModel->delete();

                    //delete seller store url rewrite
                    $ksTargetPathUrl ="multivendor/sellerprofile/sellerprofile/seller_id/".$ksData['id'].'/';
                    $this->ksSellerHelper->ksRedirectUrlDelete($ksTargetPathUrl);

                    $this->messageManager->addSuccessMessage(
                        __('A seller has been deleted successfully.')
                    );
                } catch (Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while deleting seller.'));
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while deleting seller.')
                );
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while deleting seller.')
            );
        }

        $ksResultRedirect = $this->resultRedirectFactory->create();
        return $ksResultRedirect->setPath($ksRedirectUrl);
    }
}
