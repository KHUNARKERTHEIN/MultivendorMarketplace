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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller\Ajax;

use Magento\Backend\App\Action\Context;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;

/**
 * StoreUrlVerify Controller class
 */
class StoreUrlVerify extends \Magento\Backend\App\Action
{
    /**
     * @var CollectionFactory
     */
    protected $ksSellerCollectionFactory;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param CollectionFactory $ksSellerCollectionFactory
     */
    public function __construct(
        Context $ksContext,
        CollectionFactory $ksSellerCollectionFactory
    ) {
        $this->ksSellerCollectionFactory = $ksSellerCollectionFactory;
        parent::__construct($ksContext);
    }

    /**
     * Verify seller shop URL exists or not
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        $ksSellerStoreUrl = trim($this->getRequest()->getPost("ks_seller_store_url", ""));

        $ksSellerId = $this->getRequest()->getPost("ks_seller_id");
       
        // check seller store url
        if ($ksSellerStoreUrl == "") {
            $ksStoreStore = [
                        'ks_message_type' => 'error' ,
                        'ks_message' => __('Please Enter Store URL'),
                        ];
        } else {
            $ksIsValidUrl = preg_match('/^[A-Z][A-Z0-9-\/-]*$/i', $ksSellerStoreUrl);

            if ($ksIsValidUrl == 0) {
                $ksStoreStore = [
                        'ks_message_type' => 'error' ,
                        'ks_message' => __('Only alphanumeric characters and dash(-) symbol are allowed.'),
                        ];
            } else {

                // get seller collection
                $ksCollection = $this->ksSellerCollectionFactory->create()
                                ->addFieldToFilter('ks_store_url', $ksSellerStoreUrl);

                if ($ksCollection->getSize()) {
                    foreach ($ksCollection as $ksData) {
                        if ($ksData->getKsSellerId() == $ksSellerId) {
                            $ksStoreStore = [
                                'ks_message_type' => 'success' ,
                                'ks_message' => __('Congratulations! Your Store URL is available.'),
                                ];
                        } else {
                            $ksStoreStore = [
                                'ks_message_type' => 'error' ,
                                'ks_message' => __('Your Store URL is already exist.'),
                                ];
                        }
                    }
                } else {
                    $ksStoreStore = [
                                'ks_message_type' => 'success' ,
                                'ks_message' => __('Congratulations! Your Store URL is available.'),
                                ];
                }
            }
        }

        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData($ksStoreStore);

        return $ksResponse;
    }
}
