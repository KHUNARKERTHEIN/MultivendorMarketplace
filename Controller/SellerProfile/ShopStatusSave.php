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
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory as KsSellerFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * class ShopStatusSave
 */
class ShopStatusSave extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     */
    protected $ksResultJsonFactory;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @param Context $ksContext
     * @param KsSellerFactory $ksSellerFactory
     * @param JsonFactory $ksResultJsonFactory
     */
    public function __construct(
        Context $ksContext,
        KsSellerFactory $ksSellerFactory,
        JsonFactory $ksResultJsonFactory
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        parent::__construct($ksContext);
    }

    /**
     * Save seller store status
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        //get seller id
        $ksSellerId = (int)$this->getRequest()->getPost('ks_seller_id');
        //get seller store status
        $ksStatus = $this->getRequest()->getPost('ks_status');
        //exception
        try {
            //get Collection
            $ksCollection = $this->ksSellerFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
            //set data
            $ksCollection->setKsStoreStatus($ksStatus);
            //save data
            $ksCollection->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occured while saving your data.'));
        }
        //create resultjson factory
        $result = $this->ksResultJsonFactory->create();
        //set data
        $result->setData($ksStatus);
        //return json data
        return $result;
    }
}
