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

namespace Ksolves\MultivendorMarketplace\Controller\Seller;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * StoreUrlVerify Controller class
 */
class StoreUrlVerify extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CollectionFactory
     */
    protected $ksSellerCollectionFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param CollectionFactory $ksSellerCollectionFactory
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        CollectionFactory $ksSellerCollectionFactory,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksSellerCollectionFactory = $ksSellerCollectionFactory;
        $this->ksJsonHelper = $ksJsonHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Verify vendor shop URL exists or not
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        $ksSellerStoreUrl = trim($this->getRequest()->getParam("ks_seller_store_url", ""));
        // check seller store url
        if ($ksSellerStoreUrl == "") {
            $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode(1));
        } else {
            // get seller collection
            $ksCollection = $this->ksSellerCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', ['neq' => $ksSellerId])
                            ->addFieldToFilter('ks_store_url', $ksSellerStoreUrl);

            $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode($ksCollection->getSize()));
        }
    }
}
