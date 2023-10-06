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

use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\CollectionFactory as KsFavouriteSellerCollection;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\App\Request\Http;

/**
 * MassPriceChangeAlertStatus Controller Class
 */
class MassPriceChangeAlertStatus extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $ksFilter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var KsFavouriteSellerFactory
     */
    protected $ksFavouriteSellerFactory;

    /**
     * @var KsFavouriteSellerCollection
     */
    protected $ksFavouriteSellerCollection;

    /* *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $ksRequest;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $ksFormKey;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Ui\Component\MassAction\Filter $ksFilter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param KsFavouriteSellerFactory $ksFavouriteSellerFactory
     * @param KsFavouriteSellerCollection $ksFavouriteSellerCollection
     * @param Http $ksRequest
     * @param FormKey $ksFormKey
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Ui\Component\MassAction\Filter $ksFilter,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        KsFavouriteSellerFactory $ksFavouriteSellerFactory,
        KsFavouriteSellerCollection $ksFavouriteSellerCollection,
        FormKey $ksFormKey,
        Http $ksRequest
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksDate = $ksDate;
        $this->ksFavouriteSellerFactory = $ksFavouriteSellerFactory;
        $this->ksFavouriteSellerCollection = $ksFavouriteSellerCollection;
        $this->ksRequest = $ksRequest;
        $this->ksFormKey = $ksFormKey;
        $this->ksRequest->setParam('form_key', $this->ksFormKey->getFormKey());
        parent::__construct($ksContext);
    }

    /**
     * Check Price Change Alert Status
     * Execute Action
     */
    public function execute()
    {
        $ksCount = $ksAlreadyEnabled = $ksAlreadyDisabled = 0;
        
        if ($this->getRequest()->getParam('ks_seller_price_alert') == 1) {
            $ksStatus = 'enabled';
        } else {
            $ksStatus = 'disabled';
        }

        try {
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksFavouriteSellerCollection->create());
            foreach ($ksCollection as $ksData) {
                if ($ksData->getKsSellerPriceAlert() == 1 && $this->getRequest()->getParam('ks_seller_price_alert') == 1) {
                    $ksAlreadyEnabled++;
                } elseif ($ksData->getKsSellerPriceAlert() == 0 && $this->getRequest()->getParam('ks_seller_price_alert') == 0) {
                    $ksAlreadyDisabled++;
                } else {
                    $ksData->setKsSellerPriceAlert($this->getRequest()->getParam('ks_seller_price_alert'));
                    $ksData->setKsUpdatedAt($this->ksDate->gmtDate());
                    $ksData->save();
                    $ksCount++;
                }
            }
            if ($ksCount) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 follower(s) price change alert has been %2.', $ksCount, $ksStatus)
                );
            }
            // error msg for already enabled
            if ($ksAlreadyEnabled) {
                $this->messageManager->addErrorMessage(
                    __('Price change alert of already enabled followers can\'t be '.$ksStatus.'.')
                );
            }
            // error msg for already disabled
            if ($ksAlreadyDisabled) {
                $this->messageManager->addErrorMessage(
                    __('Price change alert of already disabled followers can\'t be '.$ksStatus.'.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
