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

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\App\Request\Http;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * MassDelete Controller Class
 */
class MassDelete extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $ksRequest;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $ksFormKey;

    /**
     * @var Filter
     */
    protected $ksFilter;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var CollectionFactory
     */
    protected $ksFavouriteSellerCollectionFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CollectionFactory $ksFavouriteSellerCollectionFactory
     * @param Http $ksRequest
     * @param FormKey $ksFormKey
     *  @param Filter $ksFilter
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        CollectionFactory $ksFavouriteSellerCollectionFactory,
        Http $ksRequest,
        FormKey $ksFormKey,
        Filter $ksFilter,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksFavouriteSellerCollectionFactory = $ksFavouriteSellerCollectionFactory;
        $this->ksRequest = $ksRequest;
        $this->ksFormKey = $ksFormKey;
        $this->ksRequest->setParam('form_key', $this->ksFormKey->getFormKey());
        $this->ksFilter = $ksFilter;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Execute Mass Delete Function
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                //Get Selected Data
                $ksCollection = $this->ksFilter->getCollection($this->ksFavouriteSellerCollectionFactory->create());
                $ksCollectionSize = $ksCollection->getSize();
                // Check Collection has data
                if ($ksCollectionSize) {
                    foreach ($ksCollection as $ksRecord) {
                        $ksRecord->delete();
                    }
                    $this->messageManager->addSuccess(__("A total of %1 follower(s) has been deleted successfully.", $ksCollectionSize));
                } else {
                    $this->messageManager->addErrorMessage(
                        __('There is no such followers to be deleted.')
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
            }
            return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
